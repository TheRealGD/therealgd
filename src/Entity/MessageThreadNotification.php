<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class MessageThreadNotification extends Notification {
    /**
     * @ORM\ManyToOne(targetEntity="MessageThread", inversedBy="notifications")
     *
     * @var MessageThread
     */
    private $thread;

    public function __construct(User $receiver, MessageThread $thread) {
        parent::__construct($receiver);

        $this->thread = $thread;
    }

    public function getThread(): MessageThread {
        return $this->thread;
    }

    public function getType(): string {
        return 'message_thread';
    }
}
