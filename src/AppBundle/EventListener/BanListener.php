<?php

namespace Raddit\AppBundle\EventListener;

use Raddit\AppBundle\Controller\BanController;
use Raddit\AppBundle\Repository\BanRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Show the user a landing page if they are banned.
 */
final class BanListener implements EventSubscriberInterface {
    /**
     * @var BanRepository
     */
    private $repository;

    public function __construct(BanRepository $repository) {
        $this->repository = $repository;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();

        if ($request->isMethodIdempotent()) {
            return;
        }

        if ($this->repository->ipIsBanned($request->getClientIp())) {
            $request->attributes->set('_controller', BanController::class.'::landingPage');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 500],
        ];
    }
}
