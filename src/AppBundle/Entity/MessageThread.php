<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MessageThreadRepository")
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
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="MessageThreadNotification", mappedBy="thread", cascade={"remove"})
     */
    private $notifications;

    public function __construct(User $sender, string $body, $ip, User $receiver, string $title) {
        if (!$receiver->canBeMessagedBy($sender)) {
            throw new \DomainException('$sender cannot message $receiver');
        }

        parent::__construct($sender, $body, $ip);

        $this->receiver = $receiver;
        $this->replies = new ArrayCollection();
        $this->title = $title;
        $this->notify();
        $this->notifications = null; // remove unused field warning
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

    public function addReply(MessageReply $reply) {
        if (!$this->userCanReply($reply->getSender())) {
            throw new \DomainException('Sender is not allowed to reply');
        }

        if (!$this->replies->contains($reply)) {
            $this->replies->add($reply);
        }
    }

    public function userCanAccess($user): bool {
        return $user === $this->receiver || $user === $this->getSender();
    }

    public function userCanReply($user): bool {
        return $user === $this->receiver && $this->getSender()->canBeMessagedBy($user) ||
            $user === $this->getSender() && $this->receiver->canBeMessagedBy($user);
    }

    private function notify() {
        $notification = new MessageThreadNotification($this->receiver, $this);

        $this->receiver->sendNotification($notification);
    }
}
