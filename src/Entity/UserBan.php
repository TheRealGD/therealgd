<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserBanRepository")
 * @ORM\Table(name="user_bans")
 */
class UserBan {
    /**
     * @ORM\Column(type="uuid")
     * @ORM\Id()
     *
     * @var Uuid
     */
    private $id;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="bans")
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
        $this->user = $user;
        $this->reason = $reason;
        $this->banned = $banned;
        $this->bannedBy = $bannedBy;
        $this->expiresAt = $expiresAt;
        $this->timestamp = $timestamp ?:
            \DateTime::createFromFormat('U.u', microtime(true));
    }

    public function getId(): Uuid {
        return $this->id;
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

    public function getExpiresAt(): ?\DateTime {
        return $this->expiresAt;
    }

    public function isExpired(): bool {
        if ($this->expiresAt === null) {
            return false;
        }

        return $this->expiresAt < \DateTime::createFromFormat('U.u', microtime(true));
    }
}
