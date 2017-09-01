<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;

/**
 * aka Subraddit.
 *
 * @ORM\Entity(repositoryClass="Raddit\AppBundle\Repository\ForumRepository")
 * @ORM\Table(name="forums", indexes={
 *     @ORM\Index(name="forum_featured_idx", columns={"featured"})
 * }, uniqueConstraints={
 *     @ORM\UniqueConstraint(name="uniq_fe5e5ab8d69c0128", columns={"canonical_name"})
 * })
 */
class Forum {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id()
     *
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(type="text", unique=true)
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
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
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
     * @ORM\OneToMany(targetEntity="ForumSubscription", mappedBy="forum",
     *     cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     *
     * @var ForumSubscription[]|Collection|Selectable
     */
    private $subscriptions;

    /**
     * @ORM\OneToMany(targetEntity="ForumBan", mappedBy="forum", cascade={"persist"})
     *
     * @var ForumBan[]|Collection|Selectable
     */
    private $bans;

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
     * @ORM\ManyToOne(targetEntity="Theme")
     *
     * @var Theme|null
     */
    private $theme;

    public function __construct(
        string $name,
        string $title,
        string $description,
        string $sidebar,
        User $user = null,
        \DateTime $created = null
    ) {
        $this->setName($name);
        $this->title = $title;
        $this->description = $description;
        $this->sidebar = $sidebar;
        $this->created = $created ?: new \DateTime('@'.time());
        $this->bans = new ArrayCollection();
        $this->moderators = new ArrayCollection();
        $this->submissions = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();

        if ($user) {
            $this->addUserAsModerator($user);
            $this->subscribe($user);
        }
    }

    /**
     * @return int|null
     */
    public function getId() {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
        $this->canonicalName = self::canonicalizeName($name);
    }

    public function getCanonicalName() {
        return $this->canonicalName;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title) {
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

    public function getSidebar(): string {
        return $this->sidebar;
    }

    public function setSidebar(string $sidebar) {
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
        $criteria = Criteria::create()->orderBy(['timestamp' => 'ASC']);

        $moderators = new Pagerfanta(new DoctrineSelectableAdapter($this->moderators, $criteria));
        $moderators->setMaxPerPage($maxPerPage);
        $moderators->setCurrentPage($page);

        return $moderators;
    }

    public function userIsModerator($user, bool $adminsAreMods = true): bool {
        if (!$user instanceof User) {
            return false;
        }

        if ($adminsAreMods && $user->isAdmin()) {
            return true;
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        return count($this->moderators->matching($criteria)) > 0;
    }

    public function addUserAsModerator(User $user) {
        if (!$this->userIsModerator($user)) {
            $this->moderators->add(new Moderator($this, $user));
        }
    }

    public function userCanDelete($user): bool {
        if (!$user instanceof User) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (!$this->userIsModerator($user)) {
            return false;
        }

        return count($this->submissions) === 0;
    }

    /**
     * @return Collection|Submission[]
     */
    public function getSubmissions() {
        return $this->submissions;
    }

    public function getCreated(): \DateTime {
        return $this->created;
    }

    /**
     * @return ForumSubscription[]|Collection|Selectable
     */
    public function getSubscriptions() {
        return $this->subscriptions;
    }

    public function isSubscribed(User $user): bool {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        return count($this->subscriptions->matching($criteria)) > 0;
    }

    public function subscribe(User $user) {
        if (!$this->isSubscribed($user)) {
            $this->subscriptions->add(new ForumSubscription($user, $this));
        }
    }

    public function unsubscribe(User $user) {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        $subscription = $this->subscriptions->matching($criteria)->first();

        $this->subscriptions->removeElement($subscription);
    }

    public function userIsBanned(User $user): bool {
        if ($user->isAdmin()) {
            // should we check for mod permissions too?
            return false;
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user))
            ->orderBy(['timestamp' => 'DESC'])
            ->setMaxResults(1);

        /** @var ForumBan|null $ban */
        $ban = $this->bans->matching($criteria)->first() ?: null;

        if (!$ban || !$ban->isBan()) {
            return false;
        }

        $expiryTime = $ban->getExpiryTime();

        if ($expiryTime) {
            $now = \DateTime::createFromFormat('U.u', microtime(true));

            return $expiryTime > $now;
        }

        return true;
    }

    /**
     * @param User $user
     * @param int  $page
     * @param int  $maxPerPage
     *
     * @return Pagerfanta|ForumBan[]
     */
    public function getPaginatedBansByUser(User $user, int $page, int $maxPerPage = 25) {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user))
            ->orderBy(['timestamp' => 'DESC']);

        $pager = new Pagerfanta(new DoctrineSelectableAdapter($this->bans, $criteria));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function addBan(ForumBan $ban) {
        if (!$this->bans->contains($ban)) {
            $this->bans->add($ban);
        }
    }

    public function isFeatured(): bool {
        return $this->featured;
    }

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
     * @return Theme|null
     */
    public function getTheme() {
        return $this->theme;
    }

    /**
     * @param Theme|null $theme
     */
    public function setTheme($theme) {
        $this->theme = $theme;
    }

    public static function canonicalizeName(string $name): string {
        return mb_strtolower($name, 'UTF-8');
    }
}
