<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="moderators")
 *
 * @UniqueEntity(fields={"forum", "user"}, errorPath="user")
 */
class Moderator {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id()
     *
     * @var int
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
     * @Assert\NotNull(message="No such user.")
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
     * @return Forum
     */
    public function getForum() {
        return $this->forum;
    }

    /**
     * @param Forum $forum
     */
    public function setForum($forum) {
        $this->forum = $forum;
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
}
