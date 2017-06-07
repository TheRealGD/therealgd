<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Raddit\AppBundle\Repository\MessageThreadRepository")
 * @ORM\Table(name="message_threads")
 */
class MessageThread extends Message {
    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User|null
     */
    private $receiver;

    /**
     * @ORM\OneToMany(targetEntity="MessageReply", mappedBy="thread", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id": "ASC"})
     *
     * @var MessageReply[]|Collection|Selectable
     */
    private $replies;

    /**
     * @ORM\OneToMany(targetEntity="MessageThreadNotification", mappedBy="thread", cascade={"persist", "remove"})
     *
     * @var Notification[]|Collection|Selectable
     */
    private $notifications;

    /**
     * @ORM\Column(type="text")
     *
     * @var string|null
     */
    private $title;

    public function __construct() {
        parent::__construct();

        $this->notifications = new ArrayCollection();
        $this->replies = new ArrayCollection();
    }

    public function createReply(): MessageReply {
        $reply = new MessageReply();
        $reply->setThread($this);

        return $reply;
    }

    /**
     * @return User|null
     */
    public function getReceiver() {
        return $this->receiver;
    }

    /**
     * @param User|null $receiver
     */
    public function setReceiver($receiver) {
        $this->receiver = $receiver;
    }

    /**
     * @return MessageReply[]|Collection|Selectable
     */
    public function getReplies() {
        return $this->replies;
    }

    /**
     * @return Notification[]|Collection|Selectable
     */
    public function getNotifications() {
        return $this->notifications;
    }

    /**
     * @return string|null
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }
}
