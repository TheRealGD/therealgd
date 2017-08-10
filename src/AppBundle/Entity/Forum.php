<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * aka Subraddit.
 *
 * @ORM\Entity(repositoryClass="Raddit\AppBundle\Repository\ForumRepository")
 * @ORM\Table(name="forums", indexes={
 *     @ORM\Index(name="forum_featured_idx", columns={"featured"})
 * })
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
     * @Assert\Length(max=300)
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=1500)
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $sidebar;

    /**
     * @ORM\OneToMany(targetEntity="Moderator", mappedBy="forum", cascade={"persist", "remove"})
     *
     * @var Moderator[]|Collection
     */
    private $moderators;

    /**
     * @ORM\OneToMany(targetEntity="Submission", mappedBy="forum", cascade={"remove"}, fetch="EXTRA_LAZY")
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
     * @ORM\OneToMany(targetEntity="ForumSubscription", mappedBy="forum", cascade={"persist", "remove"})
     *
     * @var ForumSubscription[]|Collection|Selectable
     */
    private $subscriptions;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @var bool
     */
    private $featured = false;

    /**
     * @ORM\ManyToOne(targetEntity="ForumCategory", inversedBy="forums")
     *
     * @var ForumCategory|null
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Stylesheet")
     *
     * @var Stylesheet|null
     */
    private $stylesheet;

    /**
     * @ORM\ManyToOne(targetEntity="Stylesheet")
     *
     * @var Stylesheet|null
     */
    private $nightStylesheet;

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
     * @return string|null
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getSidebar() {
        return $this->sidebar;
    }

    /**
     * @param string|null $sidebar
     */
    public function setSidebar($sidebar) {
        $this->sidebar = $sidebar;
    }

    /**
     * @return Collection|Moderator[]
     */
    public function getModerators() {
        return $this->moderators;
    }

    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|Moderator[]
     */
    public function getPaginatedModerators(int $page, int $maxPerPage = 25) {
        $criteria = Criteria::create()->orderBy(['timestamp' => 'DESC']);

        $moderators = new Pagerfanta(new DoctrineSelectableAdapter($this->moderators, $criteria));
        $moderators->setMaxPerPage($maxPerPage);
        $moderators->setCurrentPage($page);

        return $moderators;
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

    /**
     * @return bool
     */
    public function isFeatured(): bool {
        return $this->featured;
    }

    /**
     * @param bool $featured
     */
    public function setFeatured(bool $featured) {
        $this->featured = $featured;
    }

    /**
     * @return null|ForumCategory
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * @param null|ForumCategory $category
     */
    public function setCategory($category) {
        $this->category = $category;
    }

    /**
     * @return Stylesheet|null
     */
    public function getStylesheet() {
        return $this->stylesheet;
    }

    /**
     * @param Stylesheet|null $stylesheet
     */
    public function setStylesheet($stylesheet) {
        $this->stylesheet = $stylesheet;
    }

    /**
     * @return Stylesheet|null
     */
    public function getNightStylesheet() {
        return $this->nightStylesheet;
    }

    /**
     * @param Stylesheet|null $nightStylesheet
     */
    public function setNightStylesheet($nightStylesheet) {
        $this->nightStylesheet = $nightStylesheet;
    }
}
