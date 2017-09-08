<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Raddit\AppBundle\Repository\NotificationRepository")
 * @ORM\Table(name="notifications")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="notification_type", type="text")
 * @ORM\DiscriminatorMap({
 *     "comment": "CommentNotification",
 *     "message_thread": "MessageThreadNotification",
 *     "message_reply": "MessageReplyNotification",
 * })
 */
abstract class Notification {
    /**
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @var int|null
     */
    private $id;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="notifications")
     *
     * @var User
     */
    private $user;

    public function __construct(User $receiver) {
        $this->user = $receiver;
    }

    abstract public function getType(): string;

    /**
     * @return int|null
     */
    public function getId() {
        return $this->id;
    }

    public function getUser(): User {
        return $this->user;
    }
}
