<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Represents a ban or unban action that applies to a user and a forum.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ForumBanRepository")
 * @ORM\Table(name="forum_bans")
 */
class ForumBan {
    /**
     * @ORM\Column(type="uuid")
     * @ORM\Id()
     *
     * @var Uuid
     */
    private $id;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Forum", inversedBy="bans")
     *
     * @var Forum
     */
    private $forum;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $reason;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $banned;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $bannedBy;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     *
     * @var \DateTime|null
     */
    private $expiresAt;

    public function __construct(
        Forum $forum,
        User $user,
        string $reason,
        bool $banned,
        User $bannedBy,
        \DateTime $expiresAt = null,
        \DateTime $timestamp = null
    ) {
        if (!$banned && $expiresAt) {
            throw new \DomainException('Unbans cannot have expiry times');
        }

        $this->id = Uuid::uuid4();
        $this->forum = $forum;
        $this->user = $user;
        $this->reason = $reason;
        $this->banned = $banned;
        $this->bannedBy = $bannedBy;
        $this->expiresAt = $expiresAt;

        // since the last ban takes precedence, and because timestamps are used
        // for sorting, we'll use microseconds to hopefully avoid collisions
        $this->timestamp = $timestamp ?:
            \DateTime::createFromFormat('U.u', microtime(true));
    }

    public function getId(): Uuid {
        return $this->id;
    }

    public function getForum(): Forum {
        return $this->forum;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getReason(): string {
        return $this->reason;
    }

    public function isBan(): bool {
        return $this->banned;
    }

    public function getBannedBy(): User {
        return $this->bannedBy;
    }

    public function getTimestamp(): \DateTime {
        return $this->timestamp;
    }

    public function getExpiryTime(): ?\DateTime {
        return $this->expiresAt;
    }

    public function isExpired() {
        if ($this->expiresAt === null) {
            return false;
        }

        return $this->expiresAt < \DateTime::createFromFormat('U.u', microtime(true));
    }
}
