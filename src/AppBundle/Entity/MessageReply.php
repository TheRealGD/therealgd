<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
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
     * @var MessageThread|null
     */
    private $thread;

    /**
     * @ORM\OneToMany(targetEntity="MessageReplyNotification", mappedBy="reply", cascade={"persist", "remove"})
     *
     * @var Notification[]|Collection|Selectable
     */
    private $notifications;

    public function __construct() {
        parent::__construct();

        $this->notifications = new ArrayCollection();
    }

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

    /**
     * @return Notification[]|Collection|Selectable
     */
    public function getNotifications() {
        return $this->notifications;
    }
}
