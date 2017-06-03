<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="message_thread_notifications")
 */
class MessageThreadNotification extends Notification {
    /**
     * @ORM\ManyToOne(targetEntity="MessageThread", inversedBy="notifications")
     *
     * @var MessageThread|null
     */
    private $thread;

    /**
     * @return MessageThread|null
     */
    public function getThread() {
        return $this->thread;
    }

    /**
     * @param MessageThread|null $thread
     */
    public function setThread($thread) {
        $this->thread = $thread;
    }

    public function getType(): string {
        return 'message_thread';
    }
}
