<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Raddit\AppBundle\Repository\BanRepository")
 * @ORM\Table(name="bans")
 */
class Ban {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @var int
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="bans")
     *
     * @var User
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

    public function __construct() {
        $this->timestamp = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIp() {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp($ip) {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getReason() {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason($reason) {
        $this->reason = $reason;
    }

    /**
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user) {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getBannedBy() {
        return $this->bannedBy;
    }

    /**
     * @param User $bannedBy
     */
    public function setBannedBy($bannedBy) {
        $this->bannedBy = $bannedBy;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     */
    public function setTimestamp(\DateTime $timestamp) {
        $this->timestamp = $timestamp;
    }

    /**
     * @return \DateTime
     */
    public function getExpiryDate() {
        return $this->expiryDate;
    }

    /**
     * @param \DateTime $expiryDate
     */
    public function setExpiryDate($expiryDate) {
        $this->expiryDate = $expiryDate;
    }
}
