<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="comments")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="comment_type", type="string")
 * @ORM\DiscriminatorMap({"top": "TopLevelComment", "reply": "ReplyComment"})
 */
abstract class Comment {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Id()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $rawBody;

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
     * @ORM\OneToMany(targetEntity="ReplyComment", mappedBy="parent")
     *
     * @var ReplyComment[]|Collection
     */
    private $children;

    public function __construct() {
        $this->timestamp = new \DateTime('@'.time());
        $this->children = new ArrayCollection();
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
     * @return string
     */
    public function getRawBody() {
        return $this->rawBody;
    }

    /**
     * @param string $rawBody
     */
    public function setRawBody($rawBody) {
        $this->rawBody = $rawBody;
    }

    /**
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody($body) {
        $this->body = $body;
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
    public function setTimestamp(\DateTime $timestamp) {
        $this->timestamp = $timestamp;
    }

    /**
     * @return ReplyComment[]
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * @param ReplyComment[] $children
     */
    public function setChildren($children) {
        $this->children = $children;
    }

    /**
     * Must return either 'top' or 'reply'.
     *
     * @return string
     */
    abstract public function getCommentType();
}
