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
     * @var User
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
     * @var string
     */
    private $title;

    public function __construct(User $sender, string $body, $ip, User $receiver, string $title) {
        parent::__construct($sender, $body, $ip);

        $this->receiver = $receiver;
        $this->replies = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->title = $title;
    }

    public function getReceiver(): User {
        return $this->receiver;
    }

    public function getTitle(): string {
        return $this->title;
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

    public function addReply(MessageReply $reply) {
        if (!$this->replies->contains($reply)) {
            $this->replies->add($reply);
        }
    }
}
