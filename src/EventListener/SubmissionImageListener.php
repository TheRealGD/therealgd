<?php

namespace App\EventListener;

use App\Entity\Submission;
use App\Events;
use Doctrine\ORM\EntityManagerInterface;
use Embed\Embed;
use Embed\Exceptions\EmbedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Download related image after submission.
 */
final class SubmissionImageListener implements EventSubscriberInterface {
    const QUEUE_KEY = 'submission_thumbnail_queue';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Client $client,
        EntityManagerInterface $entityManager,
        FilesystemInterface $filesystem,
        RequestStack $requestStack,
        ValidatorInterface $validator,
        LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
        $this->requestStack = $requestStack;
        $this->validator = $validator;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Stick every submission with a URL in a queue.
     *
     * @param GenericEvent $event
     */
    public function onNewSubmission(GenericEvent $event) {
        $request = $this->requestStack->getMasterRequest();

        /* @var Submission $submission */
        $submission = $event->getSubject();

        if (!$request || !$submission->getUrl() || $submission->getImage()) {
            return;
        }

        $queue = $request->attributes->get(self::QUEUE_KEY, []);
        $queue[] = $submission;

        $request->attributes->set(self::QUEUE_KEY, $queue);
    }

    /**
     * Loop through the queue at the end of the request and download the images.
     *
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event) {
        $queue = $event->getRequest()->attributes->get(self::QUEUE_KEY, []);

        if (!$queue) {
            return;
        }

        /* @var Submission $submission */
        foreach ($queue as $submission) {
            try {
                $embed = Embed::create($submission->getUrl());
                $imageUrl = $embed->getImage();

                if ($imageUrl) {
                    $submission->setImage($this->getFilename($imageUrl));
                }
            } catch (EmbedException $e) {
                $this->logger->info($e->getMessage());
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Download, store, and rename the image.
     *
     * @param string $imageUrl
     *
     * @return string|null the final file name, or null if the download failed
     */
    private function getFilename(string $imageUrl): ?string {
        $oldExceptionHandler = \set_error_handler(__NAMESPACE__.'\error_handler');

        try {
            $tempFile = \tempnam(\sys_get_temp_dir(), 'pml');

            $this->client->get($imageUrl, ['sink' => $tempFile]);

            $violations = $this->validator->validate(
                $tempFile,
                new Image(['detectCorrupted' => true])
            );

            if (count($violations) > 0) {
                /* @var ConstraintViolationInterface $violation */
                foreach ($violations as $violation) {
                    $message = $violation->getMessageTemplate();
                    $params = $violation->getParameters();

                    $this->logger->info($message, $params);
                }

                return null;
            }

            $mimeType = MimeTypeGuesser::getInstance()->guess($tempFile);
            $ext = ExtensionGuesser::getInstance()->guess($mimeType);

            $filename = sprintf('%s.%s', hash_file('sha256', $tempFile), $ext);

            try {
                $fh = fopen($tempFile, 'rb');
                $success = $this->filesystem->writeStream($filename, $fh);
            } catch (FileExistsException $e) {
                $success = true;
            }

            if ($success) {
                return $filename;
            }
        }
        catch (GuzzleException $e) {
            $this->logger->notice('Failed to download submission image', [
                'exception' => $e,
            ]);
        } catch (\ErrorException $e) {
            $this->logger->warning($e->getMessage(), [
                'exception' => $e,
            ]);
        } finally {
            \set_exception_handler($oldExceptionHandler);
            @\unlink($tempFile);
            @\fclose($fh);
        }

        return null;
    }

    public static function getSubscribedEvents() {
        return [
            Events::NEW_SUBMISSION => 'onNewSubmission',
            KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }
}

function error_handler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }

    throw new \ErrorException($message, 0, $severity, $file, $line);
}
