<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Serializer\Serializer;

/**
 * Suppresses redirection when a controller throws a 403 exception during an XHR
 * request.
 *
 * This is necessary because Symfony by default has the shitty, non-configurable
 * behaviour of redirecting to the login page whenever `AccessDeniedException`
 * or `AuthenticationException` objects are thrown. Making things worse,
 * XMLHttpRequest is meant to follow redirects silently, making it impossible to
 * determine if a request was truly successful.
 *
 * This listener fixes this issue by intercepting the offending exceptions and
 * sending an actual 403 response if the request happens through XHR.
 *
 * @see \Symfony\Component\Security\Http\Firewall\ExceptionListener
 */
final class AjaxListener implements EventSubscriberInterface {
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(Serializer $serializer) {
        $this->serializer = $serializer;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event) {
        $e = $event->getException();
        $request = $event->getRequest();

        if (!$e instanceof AuthenticationException && !$e instanceof AccessDeniedException) {
            return;
        }

        if (!$request->isXmlHttpRequest()) {
            return;
        }

        $format = $request->getRequestFormat();

        if ($this->serializer->supportsEncoding($format)) {
            $data = ['error' => $e->getMessage()];
            $responseBody = $this->serializer->serialize($data, $format);
        } else {
            // html and such
            $responseBody = $e->getMessage();
        }

        $event->setResponse(new Response($responseBody, 403));
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
