<?php

namespace App\Form\Model;

use App\Entity\Theme;
use App\Entity\User;
use App\Validator\Constraints\Unique;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique("normalizedUsername", idFields={"entityId": "id"}, errorPath="username",
 *     entityClass="App\Entity\User", groups={"registration", "edit"})
 */
class UserData implements UserInterface {
    /**
     * @var int|null
     */
    private $entityId;

    /**
     * @Assert\Length(min=3, max=25, groups={"registration", "edit"})
     * @Assert\NotBlank(groups={"registration", "edit"})
     * @Assert\Regex("/^\w+$/", groups={"registration", "edit"})
     *
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $normalizedUsername;

    private $password;

    /**
     * @Assert\Length(min=8, max=72, charset="8bit", groups={"registration", "edit"})
     *
     * @var string|null
     */
    private $plainPassword;

    /**
     * @Assert\Email(groups={"registration", "edit"})
     *
     * @var string|null
     */
    private $email;

    private $locale;

    /**
     * @Assert\Choice(User::FRONT_PAGE_CHOICES, groups={"settings"}, strict=true)
     * @Assert\NotBlank(groups={"settings"})
     */
    private $frontPage;

    private $nightMode;

    private $showCustomStylesheets;

    /**
     * @var Theme|null
     */
    private $preferredTheme;

    private $openExternalLinksInNewTab;

    /**
     * @Assert\Length(max=300, groups={"edit_biography"})
     *
     * @var string|null
     */
    private $biography;

    private $autoFetchSubmissionTitles;

    /**
     * @var UserGroup|null
     */
    private $group;

    public static function fromUser(User $user): self {
        $self = new self();
        $self->entityId = $user->getId();
        $self->username = $user->getUsername();
        $self->email = $user->getEmail();
        $self->locale = $user->getLocale();
        $self->frontPage = $user->getFrontPage();
        $self->nightMode = $user->isNightMode();
        $self->showCustomStylesheets = $user->isShowCustomStylesheets();
        $self->preferredTheme = $user->getPreferredTheme();
        $self->openExternalLinksInNewTab = $user->openExternalLinksInNewTab();
        $self->biography = $user->getBiography();
        $self->autoFetchSubmissionTitles = $user->autoFetchSubmissionTitles();
        $self->group = $user->getGroup();

        return $self;
    }

    public function updateUser(User $user) {
        $user->setUsername($this->username);

        if ($this->password) {
            $user->setPassword($this->password);
        }

        $user->setEmail($this->email);
        $user->setLocale($this->locale);
        $user->setFrontPage($this->frontPage);
        $user->setNightMode($this->nightMode);
        $user->setShowCustomStylesheets($this->showCustomStylesheets);
        $user->setPreferredTheme($this->preferredTheme);
        $user->setOpenExternalLinksInNewTab($this->openExternalLinksInNewTab);
        $user->setBiography($this->biography);
        $user->setAutoFetchSubmissionTitles($this->autoFetchSubmissionTitles);
        $user->setGroup($this->getGroup());
    }

    public function toUser(): User {
        $user = new User($this->username, $this->password);
        $user->setEmail($this->email);
        $user->setBiography($this->biography);
        $user->setGroup($this->group);

        $settings = [
            'showCustomStylesheets',
            'frontPage',
            'locale',
            'nightMode',
            'preferredTheme',
            'openExternalLinksInNewTab',
            'autoFetchSubmissionTitles',
        ];

        foreach ($settings as $setting) {
            if ($this->{$setting} !== null) {
                $user->{'set'.ucfirst($setting)}($this->{$setting});
            }
        }

        return $user;
    }

    /**
     * @return int|null
     */
    public function getEntityId() {
        return $this->entityId;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
        $this->normalizedUsername = isset($username) ? User::normalizeUsername($username) : null;
    }

    public function getNormalizedUsername() {
        return $this->normalizedUsername;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getPlainPassword() {
        return $this->plainPassword;
    }

    public function setPlainPassword($plainPassword) {
        $this->plainPassword = $plainPassword;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getGroup() {
        return $this->group;
    }

    public function setGroup($group) {
        $this->group = $group;
    }

    public function getLocale() {
        return $this->locale;
    }

    public function setLocale($locale) {
        $this->locale = $locale;
    }

    public function getFrontPage() {
        return $this->frontPage;
    }

    public function setFrontPage($frontPage) {
        $this->frontPage = $frontPage;
    }

    public function getNightMode() {
        return $this->nightMode;
    }

    public function setNightMode($nightMode) {
        $this->nightMode = $nightMode;
    }

    public function getShowCustomStylesheets() {
        return $this->showCustomStylesheets;
    }

    public function setShowCustomStylesheets($showCustomStylesheets) {
        $this->showCustomStylesheets = $showCustomStylesheets;
    }

    public function getPreferredTheme() {
        return $this->preferredTheme;
    }

    public function setPreferredTheme($preferredTheme) {
        $this->preferredTheme = $preferredTheme;
    }

    public function openExternalLinksInNewTab() {
        return $this->openExternalLinksInNewTab;
    }

    public function setOpenExternalLinksInNewTab($openExternalLinksInNewTab) {
        $this->openExternalLinksInNewTab = $openExternalLinksInNewTab;
    }

    public function getBiography() {
        return $this->biography;
    }

    public function setBiography($biography) {
        $this->biography = $biography;
    }

    public function getAutoFetchSubmissionTitles(): ?bool {
        return $this->autoFetchSubmissionTitles;
    }

    public function setAutoFetchSubmissionTitles(?bool $autoFetchSubmissionTitles): void {
        $this->autoFetchSubmissionTitles = $autoFetchSubmissionTitles;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles() {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt() {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials() {
        $this->plainPassword = null;
    }
}
