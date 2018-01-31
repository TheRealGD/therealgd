<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ForumRepository")
 * @ORM\Table(name="forums", indexes={
 *     @ORM\Index(name="forum_featured_idx", columns={"featured"})
 * }, uniqueConstraints={
 *     @ORM\UniqueConstraint(name="forums_name_idx", columns={"name"}),
 *     @ORM\UniqueConstraint(name="forums_normalized_name_idx", columns={"normalized_name"}),
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
    private $normalizedName;

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

    /**
     * @ORM\OneToMany(targetEntity="ForumLogEntry", mappedBy="forum", cascade={"persist", "remove"})
     * @ORM\OrderBy({"timestamp": "DESC"})
     *
     * @var ForumLogEntry[]|Collection
     */
    private $logEntries;

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
        $this->logEntries = new ArrayCollection();

        if ($user) {
            $this->addModerator(new Moderator($this, $user));
            $this->subscribe($user);
        }
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
        $this->normalizedName = self::normalizeName($name);
    }

    public function getNormalizedName(): ?string {
        return $this->normalizedName;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title) {
        $this->title = $title;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description) {
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

    public function addModerator(Moderator $moderator) {
        if (!$this->moderators->contains($moderator)) {
            $this->moderators->add($moderator);
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

        if ($subscription) {
            $this->subscriptions->removeElement($subscription);
        }
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

        return !$ban->isExpired();
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

            $this->logEntries->add(new ForumLogBan(
                $this,
                $ban->getBannedBy(),
                !$this->userIsModerator($ban->getBannedBy(), false),
                $ban,
                $ban->getTimestamp()
            ));
        }
    }

    public function isFeatured(): bool {
        return $this->featured;
    }

    public function setFeatured(bool $featured) {
        $this->featured = $featured;
    }

    public function getCategory(): ?ForumCategory {
        return $this->category;
    }

    public function setCategory(?ForumCategory $category) {
        $this->category = $category;
    }

    public function getTheme(): ?Theme {
        return $this->theme;
    }

    public function setTheme(?Theme $theme) {
        $this->theme = $theme;
    }

    /**
     * @param int $page
     * @param int $max
     *
     * @return Pagerfanta|ForumLogEntry[]
     */
    public function getPaginatedLogEntries(int $page, int $max = 50): Pagerfanta {
        $pager = new Pagerfanta(new DoctrineCollectionAdapter($this->logEntries));
        $pager->setMaxPerPage($max);
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function addLogEntry(ForumLogEntry $entry) {
        if (!$this->logEntries->contains($entry)) {
            $this->logEntries->add($entry);
        }
    }

    public static function normalizeName(string $name): string {
        return mb_strtolower($name, 'UTF-8');
    }
}
