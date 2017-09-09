<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class MessageReplyNotification extends Notification {
    // TODO: figure out why does this requires cascade={"persist"} while thread
    // notifications don't.
    /**
     * @ORM\ManyToOne(targetEntity="MessageReply", inversedBy="notifications" cascade={"persist"})
     *
     * @var MessageReply
     */
    private $reply;

    public function __construct(User $receiver, MessageReply $reply) {
        parent::__construct($receiver);

        $this->reply = $reply;
    }

    public function getReply(): MessageReply {
        return $this->reply;
    }

    public function getType(): string {
        return 'message_reply';
    }
}
