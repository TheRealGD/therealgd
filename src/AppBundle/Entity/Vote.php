<?php


namespace Raddit\AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Base class for all vote entities.
 *
 * @ORM\MappedSuperclass()
 */
abstract class Vote {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $upvote;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @ORM\JoinColumn(name="user_id", nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $user;

    public function __construct() {
        $this->timestamp = new \DateTime('@'.time());
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function isUpvote() {
        return $this->upvote;
    }

    /**
     * @param bool $upvote
     */
    public function setUpvote($upvote) {
        $this->upvote = $upvote;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     */
    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
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
}
