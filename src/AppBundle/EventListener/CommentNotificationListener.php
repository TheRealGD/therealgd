<?php

namespace Raddit\AppBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\CommentNotification;

/**
 * Listener that notifies users when someone replies to them.
 */
class CommentNotificationListener implements EventSubscriber {
    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args) {
        $comment = $args->getEntity();

        if (!$comment instanceof Comment) {
            return;
        }

        $parent = $comment->getParent();

        if ($parent && $comment->getUser() === $parent->getUser()) {
            // don't send notifications to one self
            return;
        }

        if ($parent) {
            $receiver = $comment->getParent()->getUser();
        } else {
            $receiver = $comment->getSubmission()->getUser();
        }

        $notification = new CommentNotification();
        $notification->setUser($receiver);
        $notification->setComment($comment);

        $comment->getNotifications()->add($notification);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents() {
        return ['prePersist'];
    }
}
