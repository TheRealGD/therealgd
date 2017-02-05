<?php

namespace Raddit\AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Suppresses redirection when a controller throws a 403 exception during an XHR
 * request.
 */
final class AjaxListener implements EventSubscriberInterface {
    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event) {
        $ex = $event->getException();
        $request = $event->getRequest();

        if ($ex instanceof AuthenticationException || $ex instanceof AccessDeniedException) {
            if ($request->isXmlHttpRequest()) {
                $response = new Response('', 403);
                $event->setResponse($response);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 1000],
        ];
    }
}
