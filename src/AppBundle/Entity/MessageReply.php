<?php

namespace AppBundle\Entity;

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

    /**
     * @ORM\OneToMany(targetEntity="MessageReplyNotification", mappedBy="reply", cascade={"remove"})
     */
    private $notifications;

    public function __construct(User $sender, string $body, ?string $ip, MessageThread $thread) {
        parent::__construct($sender, $body, $ip);

        $this->thread = $thread;
        $this->notify();
        $this->notifications = null; // remove unused field warning
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
