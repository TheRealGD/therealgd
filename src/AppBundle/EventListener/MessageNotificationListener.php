<?php

namespace Raddit\AppBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Raddit\AppBundle\Entity\Message;
use Raddit\AppBundle\Entity\MessageReply;
use Raddit\AppBundle\Entity\MessageReplyNotification;
use Raddit\AppBundle\Entity\MessageThread;
use Raddit\AppBundle\Entity\MessageThreadNotification;

/**
 * Sends notifications to the receiver of a message.
 */
final class MessageNotificationListener implements EventSubscriber {
    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args) {
        $message = $args->getEntity();

        if (!$message instanceof Message) {
            return;
        }

        if ($message instanceof MessageThread) {
            $notification = new MessageThreadNotification();
            $notification->setThread($message);
            $thread = $message;
        } elseif ($message instanceof MessageReply) {
            $notification = new MessageReplyNotification();
            $notification->setReply($message);
            $thread = $message->getThread();
        } else {
            throw new \LogicException();
        }

        if ($thread->getSender() === $message->getSender()) {
            $notification->setUser($thread->getReceiver());
        } else {
            $notification->setUser($thread->getSender());
        }

        $message->getNotifications()->add($notification);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents() {
        return ['prePersist'];
    }
}
