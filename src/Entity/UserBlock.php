<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_blocks", uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"blocker_id", "blocked_id"}, name="user_blocks_blocker_blocked_idx")
 * })
 */
class UserBlock {
    /**
     * @ORM\Column(type="uuid")
     * @ORM\Id()
     *
     * @var Uuid
     */
    private $id;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="blocks")
     *
     * @var User
     */
    private $blocker;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $blocked;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $comment;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $timestamp;

    public function __construct(User $blocker, User $blocked, ?string $comment) {
        if ($blocker === $blocked) {
            throw new \InvalidArgumentException();
        }

        $this->id = Uuid::uuid4();
        $this->blocker = $blocker;
        $this->blocked = $blocked;
        $this->comment = $comment;
        $this->timestamp = new \DateTime('@'.time());
    }

    public function getId(): Uuid {
        return $this->id;
    }

    public function getBlocker(): User {
        return $this->blocker;
    }

    public function getBlocked(): User {
        return $this->blocked;
    }

    public function getComment(): ?string {
        return $this->comment;
    }

    public function getTimestamp(): \DateTime {
        return $this->timestamp;
    }
}
