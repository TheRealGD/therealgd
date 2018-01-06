<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity()
 * @ORM\Table(name="forum_subscriptions", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="forum_user_idx", columns={"forum_id", "user_id"})
 * })
 */
class ForumSubscription {
    /**
     * @ORM\Column(type="uuid")
     * @ORM\Id()
     *
     * @var Uuid
     */
    private $id;

    /**
     * @ORM\JoinColumn(name="user_id", nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\JoinColumn(name="forum_id", nullable=false)
     * @ORM\ManyToOne(targetEntity="Forum", inversedBy="subscriptions")
     *
     * @var Forum
     */
    private $forum;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $subscribedAt;

    public function __construct(User $user, Forum $forum) {
        $this->id = Uuid::uuid4();
        $this->user = $user;
        $this->forum = $forum;
        $this->subscribedAt = new \DateTime('@'.time());
    }

    public function getId(): Uuid {
        return $this->id;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getForum(): Forum {
        return $this->forum;
    }

    public function getSubscribedAt(): \DateTime {
        return $this->subscribedAt;
    }
}
