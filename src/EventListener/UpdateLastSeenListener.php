<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Updates the 'last seen' field of a user. This should occur when:.
 *
 * - The user logs in.
 * - The user is already logged in via a 'remember me' cookie, but there is no
 *   session.
 *
 * Hence, the 'last seen' field does not represent the last time they loaded a
 * page, but rather an approximate time of when they started browsing.
 */
final class UpdateLastSeenListener implements EventSubscriberInterface {
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager) {
        $this->manager = $manager;
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event) {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        $user->setLastSeen(new \DateTime('@'.time()));

        // this should be safe since login occurs at the beginning of a request
        $this->manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
        ];
    }
}
