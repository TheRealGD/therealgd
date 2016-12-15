<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * aka Subraddit
 *
 * @ORM\Entity()
 * @ORM\Table(name="forums")
 */
class Forum {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
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
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Moderator", mappedBy="forum", cascade={"persist"})
     *
     * @var Moderator[]|Collection
     */
    private $moderators;

    /**
     * @ORM\OneToMany(targetEntity="Submission", mappedBy="forum")
     *
     * @var Submission[]|Collection
     */
    private $submissions;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $created;

    public function __construct() {
        $this->created = new \DateTime('@'.time());
        $this->moderators = new ArrayCollection();
        $this->submissions = new ArrayCollection();
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
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return Collection|Moderator[]
     */
    public function getModerators() {
        return $this->moderators;
    }

    /**
     * @param Collection|Moderator[] $moderators
     */
    public function setModerators($moderators) {
        $this->moderators = $moderators;
    }

    /**
     * @return Collection|Submission[]
     */
    public function getSubmissions() {
        return $this->submissions;
    }

    /**
     * @param Collection|Submission[] $submissions
     */
    public function setSubmissions($submissions) {
        $this->submissions = $submissions;
    }

    /**
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created) {
        $this->created = $created;
    }
}
