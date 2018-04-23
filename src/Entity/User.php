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
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="users_username_idx", columns={"username"}),
 *     @ORM\UniqueConstraint(name="users_normalized_username_idx", columns={"normalized_username"}),
 * })
 */
class User implements UserInterface, EquatableInterface {
    const FRONT_DEFAULT = 'default';
    const FRONT_FEATURED = 'featured';
    const FRONT_SUBSCRIBED = 'subscribed';
    const FRONT_ALL = 'all';
    const FRONT_MODERATED = 'moderated';

    const FRONT_PAGE_CHOICES = [
        self::FRONT_DEFAULT,
        self::FRONT_FEATURED,
        self::FRONT_SUBSCRIBED,
        self::FRONT_ALL,
        self::FRONT_MODERATED,
    ];

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
    private $username;

    /**
     * @ORM\Column(type="text", unique=true)
     *
     * @var string
     */
    private $normalizedUsername;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $password;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $email;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $normalizedEmail;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $created;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     *
     * @var \DateTime|null
     */
    private $lastSeen;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @var bool
     */
    private $admin = false;

    /**
     * @ORM\OneToMany(targetEntity="Moderator", mappedBy="user", fetch="EAGER")
     *
     * @var Moderator[]|Collection
     */
    private $moderatorTokens;

    /**
     * @ORM\OneToMany(targetEntity="Submission", mappedBy="user", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id": "DESC"})
     *
     * @var Submission[]|Collection|Selectable
     */
    private $submissions;

    /**
     * @ORM\OneToMany(targetEntity="SubmissionVote", mappedBy="user", fetch="EXTRA_LAZY")
     *
     * @var SubmissionVote[]|Collection
     */
    private $submissionVotes;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="user", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id": "DESC"})
     *
     * @var Comment[]|Collection|Selectable
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="CommentVote", mappedBy="user", fetch="EXTRA_LAZY")
     *
     * @var CommentVote[]|Collection
     */
    private $commentVotes;

    /**
     * @ORM\OneToMany(targetEntity="UserBan", mappedBy="user")
     *
     * @var UserBan[]|Collection|Selectable
     */
    private $bans;

    /**
     * @ORM\OneToMany(targetEntity="IpBan", mappedBy="user")
     *
     * @var IpBan[]|Collection|Selectable
     */
    private $ipBans;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $locale = 'en';

    /**
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="user", fetch="EXTRA_LAZY", cascade={"persist"})
     *
     * @var Notification[]|Collection|Selectable
     */
    private $notifications;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $nightMode = false;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     *
     * @var bool
     */
    private $showCustomStylesheets = true;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @var bool
     */
    private $trusted = false;

    /**
     * @ORM\ManyToOne(targetEntity="Theme")
     *
     * @var Theme|null
     */
    private $preferredTheme;

    /**
     * @ORM\OneToMany(targetEntity="UserBlock", mappedBy="blocker")
     * @ORM\OrderBy({"timestamp": "DESC"})
     *
     * @var UserBlock[]|Collection|Selectable
     */
    private $blocks;

    /**
     * @ORM\Column(type="text", options={"default": "default"})
     *
     * @var string
     */
    private $frontPage = self::FRONT_DEFAULT;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @var bool
     */
    private $openExternalLinksInNewTab = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $biography;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     *
     * @var bool
     */
    private $autoFetchSubmissionTitles = true;

    /**
     * @ORM\ManyToOne(targetEntity="UserGroup", inversedBy="users")
     *
     * @var UserGroup|null
     */
    private $group = null;

    public function __construct(string $username, string $password, \DateTime $created = null) {
        $this->setUsername($username);
        $this->password = $password;
        $this->created = $created ?: new \DateTime('@'.time());
        $this->notifications = new ArrayCollection();
        $this->submissions = new ArrayCollection();
        $this->submissionVotes = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->commentVotes = new ArrayCollection();
        $this->bans = new ArrayCollection();
        $this->ipBans = new ArrayCollection();
        $this->blocks = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function setUsername(string $username) {
        $this->username = $username;
        $this->normalizedUsername = self::normalizeUsername($username);
    }

    public function getNormalizedUsername(): string {
        return $this->normalizedUsername;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password) {
        $this->password = $password;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(?string $email) {
        $this->email = $email;
        $this->normalizedEmail = $email ? self::normalizeEmail($email) : null;
    }

    /**
     * Retrieve the normalized email address.
     *
     * Sending email to the normalized address is evil. Use this for lookup,
     * but *always* send to the regular, canon address.
     *
     * @return string|null
     */
    public function getNormalizedEmail(): ?string {
        return $this->normalizedEmail;
    }

    public function getCreated(): \DateTime {
        return $this->created;
    }

    public function getLastSeen(): ?\DateTime {
        return $this->lastSeen;
    }

    public function setLastSeen(?\DateTime $lastSeen) {
        $this->lastSeen = $lastSeen;
    }

    public function isAdmin(): bool {
        return $this->admin;
    }

    public function setAdmin(bool $admin) {
        $this->admin = $admin;
    }

    /**
     * @return Collection|Moderator[]
     */
    public function getModeratorTokens(): Collection {
        return $this->moderatorTokens;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles() {
        $roles = ['ROLE_USER'];

        if ($this->admin) {
            $roles[] = 'ROLE_ADMIN';
        }

        if ($this->trusted) {
            $roles[] = 'ROLE_TRUSTED_USER';
        }

        return $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt() {
        // Salt is not needed when bcrypt is used, as the password hash contains
        // the salt.
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials() {
    }

    /**
     * @return Collection|Selectable|Submission[]
     */
    public function getSubmissions(): Collection {
        return $this->submissions;
    }

    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|Comment[]
     */
    public function getPaginatedSubmissions(int $page, int $maxPerPage = 25): Pagerfanta {
        $submissions = new Pagerfanta(new DoctrineCollectionAdapter($this->submissions));
        $submissions->setMaxPerPage($maxPerPage);
        $submissions->setCurrentPage($page);

        return $submissions;
    }

    public function getSubmissionVotes(): Collection {
        return $this->submissionVotes;
    }

    /**
     * @return Collection|Selectable|Comment[]
     */
    public function getComments(): Collection {
        return $this->comments;
    }

    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|Comment[]
     */
    public function getPaginatedComments(int $page, int $maxPerPage = 25): Pagerfanta {
        $comments = new Pagerfanta(new DoctrineCollectionAdapter($this->comments));
        $comments->setMaxPerPage($maxPerPage);
        $comments->setCurrentPage($page);

        return $comments;
    }

    public function getCommentVotes(): Collection {
        return $this->commentVotes;
    }

    /**
     * @return UserBan[]|Collection
     */
    public function getBans(): Collection {
        return $this->bans;
    }

    public function addBan(UserBan $ban) {
        if (!$this->bans->contains($ban)) {
            $this->bans->add($ban);
        }
    }

    public function isBanned(): bool {
        $criteria = Criteria::create()
            ->orderBy(['timestamp' => 'DESC'])
            ->setMaxResults(1);

        /* @var UserBan $ban */
        $ban = $this->bans->matching($criteria)->first() ?: null;

        return $ban && $ban->isBan() && !$ban->isExpired();
    }

    /**
     * @return Collection|IpBan[]
     */
    public function getIpBans(): Collection {
        return $this->ipBans;
    }

    public function getLocale(): string {
        return $this->locale;
    }

    public function setLocale(string $locale) {
        $this->locale = $locale;
    }

    /**
     * @return Collection|Selectable|Notification[]
     */
    public function getNotifications(): Collection {
        return $this->notifications;
    }

    public function sendNotification(Notification $notification) {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
        }
    }

    private function _unreadNotificationsCriteria(Criteria $existingCriteria = null):Criteria {
      $criteria = ($existingCriteria !== null) ? $existingCriteria : Criteria::create();
      $expr = Criteria::expr();
      $criteria = $criteria->where($expr->eq('read', false));
      return $criteria;
    }

    /**
     * @return Collection|Selectable|Notification[]
     */
    public function getUnreadNotifications(): Collection {
        $notifs = $this->notifications;
        return $notifs->matching($this->_unreadNotificationsCriteria());
    }

    /**
     * @param string $filter unread|all
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|Notification[]
     */
    public function getPaginatedNotifications(string $filter, int $page, int $maxPerPage = 25): Pagerfanta {
        $criteria = Criteria::create()->orderBy(['id' => 'DESC']);
        if ($filter === 'unread') {
          $criteria = $this->_unreadNotificationsCriteria($criteria);
        }

        $notifications = new Pagerfanta(new DoctrineSelectableAdapter($this->notifications, $criteria));
        $notifications->setMaxPerPage($maxPerPage);
        $notifications->setCurrentPage($page);

        return $notifications;
    }

    public function isNightMode(): bool {
        return $this->nightMode;
    }

    public function setNightMode(bool $nightMode) {
        $this->nightMode = $nightMode;
    }

    public function isShowCustomStylesheets(): bool {
        return $this->showCustomStylesheets;
    }

    public function setShowCustomStylesheets(bool $showCustomStylesheets) {
        $this->showCustomStylesheets = $showCustomStylesheets;
    }

    public function isTrusted(): bool {
        return $this->trusted;
    }

    public function isTrustedOrAdmin(): bool {
        return $this->admin || $this->trusted;
    }

    public function setTrusted(bool $trusted) {
        $this->trusted = $trusted;
    }

    public function getPreferredTheme(): ?Theme {
        return $this->preferredTheme;
    }

    public function setPreferredTheme(?Theme $preferredTheme) {
        $this->preferredTheme = $preferredTheme;
    }

    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|UserBlock[]
     */
    public function getPaginatedBlocks(int $page, int $maxPerPage = 25) {
        $pager = new Pagerfanta(new DoctrineCollectionAdapter($this->blocks));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function addBlock(UserBlock $block) {
        if (!$this->blocks->contains($block)) {
            $this->blocks->add($block);
        }
    }

    public function isBlocking(self $user): bool {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('blocked', $user));

        return count($this->blocks->matching($criteria)) > 0;
    }

    public function canBeMessagedBy($user): bool {
        if (!$user instanceof self) {
            return false;
        }

        return $user->isAdmin() || !$this->isBlocking($user);
    }

    public function getFrontPage(): string {
        return $this->frontPage;
    }

    public function setFrontPage(string $frontPage) {
        if (!in_array($frontPage, self::FRONT_PAGE_CHOICES, true)) {
            throw new \InvalidArgumentException('Unknown choice');
        }

        $this->frontPage = $frontPage;
    }

    public function openExternalLinksInNewTab(): bool {
        return $this->openExternalLinksInNewTab;
    }

    public function setOpenExternalLinksInNewTab(bool $openExternalLinksInNewTab) {
        $this->openExternalLinksInNewTab = $openExternalLinksInNewTab;
    }

    public function getBiography(): ?string {
        return $this->biography;
    }

    public function setBiography(?string $biography) {
        $this->biography = $biography;
    }

    public function getGroup(): ?UserGroup {
        return $this->group;
    }

    public function setGroup(?UserGroup $group) {
        $this->group = $group;
    }

    public function autoFetchSubmissionTitles(): bool {
        return $this->autoFetchSubmissionTitles;
    }

    public function setAutoFetchSubmissionTitles(bool $autoFetchSubmissionTitles): void {
        $this->autoFetchSubmissionTitles = $autoFetchSubmissionTitles;
    }

    /**
     * Returns the normalized form of the username.
     *
     * @param string $username
     *
     * @return string
     */
    public static function normalizeUsername(string $username): string {
        return mb_strtolower($username, 'UTF-8');
    }

    /**
     * @param string $email
     *
     * @return string
     *
     * @throws \InvalidArgumentException if `$email` is not a valid address
     */
    public static function normalizeEmail(string $email): string {
        if (substr_count($email, '@') !== 1) {
            throw new \InvalidArgumentException('Invalid email address');
        }

        list($username, $domain) = explode('@', $email, 2);

        switch (strtolower($domain)) {
        case 'gmail.com':
        case 'googlemail.com':
            $username = strtolower($username);
            $username = str_replace('.', '', $username);
            $username = preg_replace('/\+.*/', '', $username);
            $domain = 'gmail.com';
            break;
        // TODO - other common email providers
        default:
            // TODO - do unicode domains need to be handled too?
            $domain = strtolower($domain);
        }

        return sprintf('%s@%s', $username, $domain);
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(UserInterface $user) {
        return $user instanceof self &&
            $this->id === $user->id &&
            $this->username === $user->username &&
            hash_equals($this->password, $user->password) &&
            $this->admin === $user->admin &&
            $this->trusted === $user->trusted;
    }
}
