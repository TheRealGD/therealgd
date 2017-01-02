<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Raddit\AppBundle\Utils\CanonicalizableInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * aka Subraddit
 *
 * @ORM\Entity()
 * @ORM\Table(name="forums")
 *
 * @UniqueEntity("canonicalName", errorPath="name")
 */
class Forum implements CanonicalizableInterface {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="text", unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=3, max=25)
     * @Assert\Regex("/^\w+$/")
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="text", unique=true)
     *
     * @var string
     */
    private $canonicalName;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\Length(max=100)
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=1500)
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $description;

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
     * @return string
     */
    public function getCanonicalName() {
        return $this->canonicalName;
    }

    /**
     * @param string $canonicalName
     */
    public function setCanonicalName($canonicalName) {
        $this->canonicalName = $canonicalName;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
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

    /**
     * {@inheritdoc}
     */
    public function getCanonicalizableFields() {
        return [
            'name' => 'canonicalName',
        ];
    }
}
