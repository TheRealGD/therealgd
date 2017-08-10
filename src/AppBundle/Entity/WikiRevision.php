<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="wiki_revisions")
 */
class WikiRevision {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     *
     * @var string|null
     */
    private $body;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="WikiPage", inversedBy="revisions")
     *
     * @var WikiPage|null
     */
    private $page;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User|null
     */
    private $user;

    public function __construct() {
        $this->timestamp = new \DateTime();
    }

    /**
     * @return int|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @return WikiPage|null
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * @param WikiPage|null $page
     */
    public function setPage($page) {
        $this->page = $page;
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
     * @return User|null
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser($user) {
        $this->user = $user;
    }
}
