<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="submissions")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="submission_type", type="string")
 * @ORM\DiscriminatorMap({"url": "Url", "post": "Post"})
 */
abstract class Submission {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="submission")
     * @var Comment[]|Collection
     */
    private $comments;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $timestamp;

    public function __construct() {
        $this->comments = new ArrayCollection();
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
     * @return Collection|Comment[]
     */
    public function getComments() {
        return $this->comments;
    }

    /**
     * @param Collection|Comment[] $comments
     */
    public function setComments($comments) {
        $this->comments = $comments;
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
     * Must return 'url' or 'post'.
     *
     * @return string
     */
    abstract public function getSubmissionType();
}
