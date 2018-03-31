<?php

namespace App\EventListener;

use App\Entity\Comment;
use App\Entity\Forum;
use App\Entity\ForumWebhook;
use App\Entity\Submission;
use App\Event\EntityModifiedEvent;
use App\Events;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

final class WebhookListener implements EventSubscriberInterface {
    private const QUEUE_KEY = 'webhook_queue';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var bool
     */
    private $webhooksEnabled;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Client $client,
        RequestStack $requestStack,
        SerializerInterface $serializer,
        bool $webhooksEnabled,
        LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->requestStack = $requestStack;
        $this->serializer = $serializer;
        $this->webhooksEnabled = $webhooksEnabled;
        $this->logger = $logger ?: new NullLogger();
    }

    public function onKernelTerminate(PostResponseEvent $event): void {
        if (
            !$this->webhooksEnabled ||
            $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST ||
            !$event->getRequest()->attributes->has(self::QUEUE_KEY)
        ) {
            return;
        }

        // request not available from stack after kernel.terminate
        $queue = $this->getQueue($event->getRequest());

        if ($queue->isEmpty()) {
            return;
        }

        $requests = function () use ($queue) {
            while (!$queue->isEmpty()) {
                $item = $queue->pop();

                /* @var ForumWebhook $webhook */
                $webhook = $item['webhook'];

                if ($webhook->getSecretToken() !== null) {
                    $headers['X-Postmill-Secret'] = $webhook->getSecretToken();
                }

                $body = $this->serializer->serialize([
                    'event' => $webhook->getEvent(),
                    'subject' => $item['subject'],
                ], 'json');

                yield new GuzzleRequest('POST', $webhook->getUrl(), $headers ?? [], $body);
            }
        };

        $pool = new Pool($this->client, $requests(), [
            'concurrency' => 5,
            'rejected' => function ($reason) {
                // todo - log so forum mods can see this
                $this->logger->warning('Webhook failed', [
                    'reason' => $reason,
                ]);
            },
        ]);

        $pool->promise()->wait();
    }

    public function onNewSubmission(GenericEvent $event): void {
        /* @var Submission $subject */
        $subject = $event->getSubject();
        $forum = $subject->getForum();

        $this->addToQueue($forum, ForumWebhook::EVENT_NEW_SUBMISSION, $subject);
    }

    public function onEditSubmission(EntityModifiedEvent $event): void {
        /* @var Submission $subject */
        $subject = $event->getAfter();
        $forum = $subject->getForum();

        $this->addToQueue($forum, ForumWebhook::EVENT_EDIT_SUBMISSION, [
            'before' => $event->getBefore(),
            'after' => $event->getAfter(),
        ]);
    }

    public function onNewComment(GenericEvent $event): void {
        /* @var Comment $subject */
        $subject = $event->getSubject();
        $forum = $subject->getSubmission()->getForum();

        $this->addToQueue($forum, ForumWebhook::EVENT_NEW_COMMENT, $subject);
    }

    public function onEditComment(EntityModifiedEvent $event): void {
        /* @var Comment $subject */
        $subject = $event->getAfter();
        $forum = $subject->getSubmission()->getForum();

        $this->addToQueue($forum, ForumWebhook::EVENT_EDIT_COMMENT, [
            'before' => $event->getBefore(),
            'after' => $event->getAfter(),
        ]);
    }

    private function getQueue(Request $request = null): \SplStack {
        $attributes = ($request ?: $this->requestStack->getMasterRequest())->attributes;

        $queue = $attributes->get(self::QUEUE_KEY, new \SplStack());

        if (!$attributes->has(self::QUEUE_KEY)) {
            $attributes->set(self::QUEUE_KEY, $queue);
        }

        return $queue;
    }

    private function addToQueue(Forum $forum, string $eventName, $subject): void {
        $webhooks = $forum->getWebhooksByEvent($eventName);
        $queue = $this->getQueue();

        foreach ($webhooks as $webhook) {
            $queue->push(['webhook' => $webhook, 'subject' => $subject]);
        }
    }

    public static function getSubscribedEvents(): array {
        return [
            Events::NEW_SUBMISSION => 'onNewSubmission',
            Events::EDIT_SUBMISSION => 'onEditSubmission',
            Events::NEW_COMMENT => 'onNewComment',
            Events::EDIT_COMMENT => 'onEditComment',
            KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }
}
