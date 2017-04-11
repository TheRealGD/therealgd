<?php

namespace Raddit\AppBundle\EventListener;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Raddit\AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * @see http://symfony.com/doc/current/session/locale_sticky_session.html
 */
final class LocaleListener {
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        SessionInterface $session,
        TokenStorageInterface $tokenStorage
    ) {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        if ($request->getSession()->has('_locale')) {
            $request->setLocale($request->getSession()->get('_locale'));
        }
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event) {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof User) {
            $this->configureLocale($user);
        }
    }

    public function postPersist(LifecycleEventArgs $args) {
        $user = $args->getEntity();

        if ($user instanceof User) {
            $this->configureLocale($user);
        }
    }

    public function postUpdate(LifecycleEventArgs $args) {
        $this->postPersist($args);
    }

    private function configureLocale(User $user) {
        if (
            !$this->session->isStarted() ||
            !$this->tokenStorage->getToken() ||
            $this->tokenStorage->getToken()->getUser() !== $user
        ) {
            // The session is not started, or the logged in user is not the
            // account being modified.
            return;
        }

        if ($user->getLocale() !== null) {
            $this->session->set('_locale', $user->getLocale());
        } else {
            $this->session->remove('_locale');
        }
    }
}
