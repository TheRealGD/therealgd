<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 */
abstract class Message {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @var int|null
     */
    private $id;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User|null
     */
    private $sender;

    /**
     * @ORM\Column(type="text")
     *
     * @var string|null
     */
    private $body;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @ORM\Column(type="inet")
     *
     * @var string|null
     */
    private $ip;

    public function __construct() {
        $this->timestamp = new \DateTime('@'.time());
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
    public function getSender() {
        return $this->sender;
    }

    /**
     * @param User|null $sender
     */
    public function setSender($sender) {
        $this->sender = $sender;
    }

    /**
     * @return string|null
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @param string|null $body
     */
    public function setBody($body) {
        $this->body = $body;
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
     * @return string|null
     */
    public function getIp() {
        return $this->ip;
    }

    /**
     * @param string|null $ip
     */
    public function setIp($ip) {
        $this->ip = $ip;
    }

    /**
     * @return Notification[]|Collection|Selectable
     */
    abstract public function getNotifications();
}
