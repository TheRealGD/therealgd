<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * aka Subraddit.
 *
 * @ORM\Entity()
 * @ORM\Table(name="forums")
 *
 * @UniqueEntity("canonicalName", errorPath="name")
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
     * @ORM\Column(type="text", unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=3, max=25)
     * @Assert\Regex("/^\w+$/", message="The name must contain only contain letters, numbers, and underscores.")
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
     * @ORM\OneToMany(targetEntity="Moderator", mappedBy="forum", cascade={"persist", "remove"})
     *
     * @var Moderator[]|Collection
     */
    private $moderators;

    /**
     * @ORM\OneToMany(targetEntity="Submission", mappedBy="forum", cascade={"remove"})
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

    /**
     * @ORM\OneToMany(targetEntity="ForumSubscription", mappedBy="forum")
     *
     * @var ForumSubscription[]|Collection|Selectable
     */
    private $subscriptions;

    public function __construct() {
        $this->created = new \DateTime('@'.time());
        $this->moderators = new ArrayCollection();
        $this->submissions = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
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
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
        $this->canonicalName = mb_strtolower($name, 'UTF-8');
    }

    /**
     * @return string
     */
    public function getCanonicalName() {
        return $this->canonicalName;
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
     * @param Moderator|Moderator[]|\Traversable $moderator
     */
    public function addModerator($moderator) {
        $moderators = is_iterable($moderator) ? $moderator : [$moderator];

        foreach ($moderators as $item) {
            $this->moderators->add($item);
        }
    }

    /**
     * @param Moderator|Moderator[]|\Traversable $moderator
     */
    public function removeModerator($moderator) {
        $moderators = is_iterable($moderator) ? $moderator : [$moderator];

        foreach ($moderators as $item) {
            $this->moderators->removeElement($item);
        }
    }

    /**
     * @return Collection|Submission[]
     */
    public function getSubmissions() {
        return $this->submissions;
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
     * @return ForumSubscription[]|Collection|Selectable
     */
    public function getSubscriptions() {
        return $this->subscriptions;
    }
}
