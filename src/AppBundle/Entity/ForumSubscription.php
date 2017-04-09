<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="forum_subscriptions", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="forum_user_idx", columns={"forum_id", "user_id"})
 * })
 */
class ForumSubscription {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\JoinColumn(name="user_id", nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="subscriptions")
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

    public function __construct() {
        $this->subscribedAt = new \DateTime('@'.time());
    }

    /**
     * @return int|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user) {
        $this->user = $user;
    }

    /**
     * @return Forum|null
     */
    public function getForum() {
        return $this->forum;
    }

    /**
     * @param Forum $forum
     */
    public function setForum(Forum $forum) {
        $this->forum = $forum;
    }

    /**
     * @return \DateTime
     */
    public function getSubscribedAt(): \DateTime {
        return $this->subscribedAt;
    }

    /**
     * @param \DateTime $subscribedAt
     */
    public function setSubscribedAt(\DateTime $subscribedAt) {
        $this->subscribedAt = $subscribedAt;
    }
}
