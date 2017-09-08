<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="message_replies")
 */
class MessageReply extends Message {
    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="MessageThread", inversedBy="replies")
     *
     * @var MessageThread
     */
    private $thread;

    public function __construct(User $sender, string $body, $ip, MessageThread $thread) {
        parent::__construct($sender, $body, $ip);

        $this->thread = $thread;
        $this->notify();
    }

    public function getThread(): MessageThread {
        return $this->thread;
    }

    public function notify() {
        if ($this->getSender() === $this->thread->getSender()) {
            $receiver = $this->thread->getReceiver();
        } else {
            $receiver = $this->thread->getSender();
        }

        $receiver->sendNotification(new MessageReplyNotification($receiver, $this));
    }
}
