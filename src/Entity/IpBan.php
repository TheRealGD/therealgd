<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\IpBanRepository")
 * @ORM\Table(name="bans")
 */
class IpBan {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(type="inet")
     *
     * @var string
     */
    private $ip;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $reason;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="ipBans")
     *
     * @var User|null
     */
    private $user;

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
     * @var \DateTime
     */
    private $expiryDate;

    public function __construct(
        string $ip,
        string $reason,
        ?User $user,
        User $bannedBy,
        \DateTime $expiryDate = null,
        \DateTime $timestamp = null
    ) {
        $this->ip = $ip;
        $this->reason = $reason;
        $this->user = $user;
        $this->bannedBy = $bannedBy;
        $this->expiryDate = $expiryDate;
        $this->timestamp = $timestamp ?: new \DateTime();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getIp(): string {
        return $this->ip;
    }

    public function getReason(): string {
        return $this->reason;
    }

    public function getUser(): ?User {
        return $this->user;
    }

    public function getBannedBy(): User {
        return $this->bannedBy;
    }

    public function getTimestamp(): \DateTime {
        return $this->timestamp;
    }

    public function getExpiryDate(): ?\DateTime {
        return $this->expiryDate;
    }
}
