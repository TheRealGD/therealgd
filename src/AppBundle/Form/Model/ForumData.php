<?php

namespace Raddit\AppBundle\Form\Model;

use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Validator\Constraints\UniqueForum;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueForum(groups={"create", "edit"})
 */
class ForumData {
    private $entityId;

    /**
     * @Assert\NotBlank(groups={"create", "edit"})
     * @Assert\Length(min=3, max=25, groups={"create", "edit"})
     * @Assert\Regex("/^\w+$/",
     *     message="The name must contain only contain letters, numbers, and underscores.",
     *     groups={"create", "edit"}
     * )
     */
    private $name;

    /**
     * @Assert\Length(max=100, groups={"create", "edit"})
     * @Assert\NotBlank(groups={"create", "edit"})
     */
    private $title;

    /**
     * @Assert\Length(max=1500, groups={"create", "edit"})
     * @Assert\NotBlank(groups={"create", "edit"})
     */
    private $sidebar;

    /**
     * @Assert\Length(max=300, groups={"create", "edit"})
     * @Assert\NotBlank(groups={"create", "edit"})
     */
    private $description;

    private $featured = false;

    private $theme;

    private $category;

    public static function createFromForum(Forum $forum): self {
        $self = new self();
        $self->entityId = $forum->getId();
        $self->name = $forum->getName();
        $self->title = $forum->getTitle();
        $self->sidebar = $forum->getSidebar();
        $self->description = $forum->getDescription();
        $self->featured = $forum->isFeatured();
        $self->theme = $forum->getTheme();
        $self->category = $forum->getCategory();

        return $self;
    }

    public function toForum(User $user): Forum {
        $forum = new Forum(
            $this->name,
            $this->title,
            $this->description,
            $this->sidebar,
            $user
        );

        $forum->setFeatured($this->featured);
        $forum->setTheme($this->theme);
        $forum->setCategory($this->category);

        return $forum;
    }

    public function updateForum(Forum $forum) {
        $forum->setName($this->name);
        $forum->setTitle($this->title);
        $forum->setSidebar($this->sidebar);
        $forum->setDescription($this->description);
        $forum->setFeatured($this->featured);
        $forum->setTheme($this->theme);
        $forum->setCategory($this->category);
    }

    public function getEntityId() {
        return $this->entityId;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getSidebar() {
        return $this->sidebar;
    }

    public function setSidebar($sidebar) {
        $this->sidebar = $sidebar;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function isFeatured(): bool {
        return $this->featured;
    }

    public function setFeatured(bool $featured) {
        $this->featured = $featured;
    }

    public function getTheme() {
        return $this->theme;
    }

    public function setTheme($theme) {
        $this->theme = $theme;
    }

    public function getCategory() {
        return $this->category;
    }

    public function setCategory($category) {
        $this->category = $category;
    }
}
