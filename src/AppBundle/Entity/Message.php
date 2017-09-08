<?php

namespace Raddit\AppBundle\Entity;

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
     * @var User
     */
    private $sender;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
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

    /**
     * Message constructor.
     *
     * @param User        $sender
     * @param string      $body
     * @param string|null $ip
     */
    public function __construct(User $sender, string $body, $ip) {
        if ($ip !== null && !filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException('$ip must be valid IP address or NULL');
        }

        $this->sender = $sender;
        $this->body = $body;
        $this->ip = $ip;
        $this->timestamp = new \DateTime('@'.time());
    }

    /**
     * @return int|null
     */
    public function getId() {
        return $this->id;
    }

    public function getSender(): User {
        return $this->sender;
    }

    public function getBody(): string {
        return $this->body;
    }

    public function getTimestamp(): \DateTime {
        return $this->timestamp;
    }

    /**
     * @return string|null
     */
    public function getIp() {
        return $this->ip;
    }
}
