<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="message_reply_notifications")
 */
class MessageReplyNotification extends Notification {
    /**
     * @ORM\ManyToOne(targetEntity="MessageReply", inversedBy="notifications")
     *
     * @var MessageReply|null
     */
    private $reply;

    /**
     * @return MessageReply|null
     */
    public function getReply() {
        return $this->reply;
    }

    /**
     * @param MessageReply|null $reply
     */
    public function setReply($reply) {
        $this->reply = $reply;
    }

    public function getType(): string {
        return 'message_reply';
    }
}
