<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Raddit\AppBundle\Repository\UserRepository")
 * @ORM\Table(name="users")
 *
 * @UniqueEntity("canonicalUsername", errorPath="username")
 */
class User implements UserInterface, TwoFactorInterface {
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
     * @Assert\Length(min=3, max=25)
     * @Assert\NotBlank()
     * @Assert\Regex("/^\w+$/")
     *
     * @var string
     */
    private $username;

    /**
     * @ORM\Column(type="text", unique=true)
     *
     * @var string
     */
    private $canonicalUsername;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $password;

    /**
     * Note: bcrypt cannot handle more than 72 bytes.
     *
     * @Assert\Length(min=8, max=72, charset="8bit")
     * @Assert\NotBlank(groups={"registration"})
     *
     * @var string
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Email()
     *
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $canonicalEmail;

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
     * @ORM\OneToMany(targetEntity="Moderator", mappedBy="user")
     *
     * @var Moderator[]|Collection
     */
    private $moderatorTokens;

    /**
     * @ORM\OneToMany(targetEntity="Submission", mappedBy="user")
     * @ORM\OrderBy({"id": "DESC"})
     *
     * @var Submission[]|Collection|Selectable
     */
    private $submissions;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="user")
     * @ORM\OrderBy({"id": "DESC"})
     *
     * @var Comment[]|Collection|Selectable
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="Ban", mappedBy="user")
     *
     * @var Ban[]|Collection|Selectable
     */
    private $bans;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $locale = 'en';

    /**
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="user", fetch="EXTRA_LAZY")
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
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Assert\Expression("value === false || this.getEmail() !== null",
     *     message="Two-factor cannot be enabled without providing an email address.",
     *     groups={"editing"})
     *
     * @var bool
     */
    private $twoFactorEnabled = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $emailAuthCode;

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

    public function __construct() {
        $this->created = new \DateTime('@'.time());
        $this->notifications = new ArrayCollection();
        $this->submissions = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->bans = new ArrayCollection();
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
    public function getUsername() {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username) {
        $this->username = $username;
        $this->canonicalUsername = self::canonicalizeUsername($username);
    }

    /**
     * @return string
     */
    public function getCanonicalUsername() {
        return $this->canonicalUsername;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPlainPassword() {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword($plainPassword) {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
        $this->canonicalEmail = $email ? self::canonicalizeEmail($email) : null;
    }

    /**
     * Retrieve the canonical email address.
     *
     * Sending email to the canonicalised address is evil. Use this for lookup,
     * but *always* send to the regular, non-canon address.
     *
     * @return string
     */
    public function getCanonicalEmail() {
        return $this->canonicalEmail;
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
     * @return \DateTime|null
     */
    public function getLastSeen() {
        return $this->lastSeen;
    }

    /**
     * @param \DateTime|null $lastSeen
     */
    public function setLastSeen($lastSeen) {
        $this->lastSeen = $lastSeen;
    }

    /**
     * @return bool
     */
    public function isAdmin() {
        return $this->admin;
    }

    /**
     * @param bool $admin
     */
    public function setAdmin($admin) {
        $this->admin = $admin;
    }

    /**
     * @return Collection|Moderator[]
     */
    public function getModeratorTokens() {
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
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials() {
        $this->plainPassword = null;
    }

    /**
     * @return Collection|Selectable|Submission[]
     */
    public function getSubmissions() {
        return $this->submissions;
    }

    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|Comment[]
     */
    public function getPaginatedSubmissions(int $page, int $maxPerPage = 25) {
        $submissions = new Pagerfanta(new DoctrineCollectionAdapter($this->submissions));
        $submissions->setMaxPerPage($maxPerPage);
        $submissions->setCurrentPage($page);

        return $submissions;
    }

    /**
     * @return Collection|Selectable|Comment[]
     */
    public function getComments() {
        return $this->comments;
    }

    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|Comment[]
     */
    public function getPaginatedComments(int $page, int $maxPerPage = 25) {
        $comments = new Pagerfanta(new DoctrineCollectionAdapter($this->comments));
        $comments->setMaxPerPage($maxPerPage);
        $comments->setCurrentPage($page);

        return $comments;
    }

    /**
     * @return Collection|Selectable|Ban[]
     */
    public function getBans() {
        return $this->bans;
    }

    /**
     * Checks if a user is banned.
     *
     * @return bool
     */
    public function isBanned(): bool {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('expiryDate', null))
            ->orWhere(Criteria::expr()->gte('expiryDate', new \DateTime('@'.time())))
            ->setMaxResults(1);

        return count($this->bans->matching($criteria)) > 0;
    }

    /**
     * @return string|null
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * @param string|null $locale
     */
    public function setLocale($locale) {
        $this->locale = $locale;
    }

    /**
     * @return Collection|Selectable|Notification[]
     */
    public function getNotifications() {
        return $this->notifications;
    }

    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|Notification[]
     */
    public function getPaginatedNotifications(int $page, int $maxPerPage = 25) {
        $criteria = Criteria::create()->orderBy(['id' => 'DESC']);

        $notifications = new Pagerfanta(new DoctrineSelectableAdapter($this->notifications, $criteria));
        $notifications->setMaxPerPage($maxPerPage);
        $notifications->setCurrentPage($page);

        return $notifications;
    }

    /**
     * @return bool
     */
    public function isNightMode(): bool {
        return $this->nightMode;
    }

    /**
     * @param bool $nightMode
     */
    public function setNightMode(bool $nightMode) {
        $this->nightMode = $nightMode;
    }

    /**
     * @return bool
     */
    public function isTwoFactorEnabled(): bool {
        return $this->twoFactorEnabled;
    }

    /**
     * @param bool $twoFactorEnabled
     */
    public function setTwoFactorEnabled(bool $twoFactorEnabled) {
        $this->twoFactorEnabled = $twoFactorEnabled;
    }

    /**
     * @return bool
     */
    public function isEmailAuthEnabled() {
        return $this->email !== null && $this->twoFactorEnabled;
    }

    /**
     * @return int|null
     */
    public function getEmailAuthCode() {
        return $this->emailAuthCode;
    }

    /**
     * @param int|null $authCode
     */
    public function setEmailAuthCode($authCode) {
        $this->emailAuthCode = $authCode;
    }

    /**
     * @return bool
     */
    public function isShowCustomStylesheets(): bool {
        return $this->showCustomStylesheets;
    }

    /**
     * @param bool $showCustomStylesheets
     */
    public function setShowCustomStylesheets(bool $showCustomStylesheets) {
        $this->showCustomStylesheets = $showCustomStylesheets;
    }

    /**
     * @return bool
     */
    public function isTrusted(): bool {
        return $this->trusted;
    }

    /**
     * @param bool $trusted
     */
    public function setTrusted(bool $trusted) {
        $this->trusted = $trusted;
    }

    /**
     * Returns the canonical form of the username.
     *
     * @param string $username
     *
     * @return string
     */
    public static function canonicalizeUsername(string $username): string {
        return mb_strtolower($username, 'UTF-8');
    }

    /**
     * @param string $email
     *
     * @return string
     *
     * @throws \InvalidArgumentException if `$email` is not a valid address
     */
    public static function canonicalizeEmail(string $email): string {
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
}
