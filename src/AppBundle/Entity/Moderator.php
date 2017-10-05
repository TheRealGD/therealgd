<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity()
 * @ORM\Table(name="moderators", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="moderator_forum_user_idx", columns={"forum_id", "user_id"})
 * })
 */
class Moderator {
    /**
     * @ORM\Column(type="uuid")
     * @ORM\Id()
     *
     * @var Uuid
     */
    private $id;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Forum", inversedBy="moderators")
     *
     * @var Forum
     */
    private $forum;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="moderatorTokens")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $timestamp;

    public function __construct(Forum $forum, User $user) {
        $this->id = Uuid::uuid4();
        $this->forum = $forum;
        $this->user = $user;
        $this->timestamp = new \DateTime('@'.time());
        $forum->addModerator($this);
    }

    public function getId(): Uuid {
        return $this->id;
    }

    /**
     * @return Forum
     */
    public function getForum() {
        return $this->forum;
    }

    /**
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime {
        return $this->timestamp;
    }
}
