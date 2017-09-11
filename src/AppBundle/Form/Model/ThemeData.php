<?php

namespace Raddit\AppBundle\Form\Model;

use Raddit\AppBundle\Entity\Theme;
use Raddit\AppBundle\Entity\ThemeRevision;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Validator\Constraints\Css;
use Raddit\AppBundle\Validator\Constraints\UniqueTheme;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueTheme()
 */
class ThemeData {
    /**
     * @var Uuid|null
     */
    private $entityId;

    public $author;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=50)
     *
     * @var string|null
     */
    public $name;

    /**
     * @Assert\Expression("value || this.dayCss || this.nightCss",
     *     message="At least one CSS field must be filled.")
     * @Assert\Length(max=100000)
     * @Css()
     *
     * @var string|null
     */
    public $commonCss;

    /**
     * @Assert\Length(max=100000)
     * @Css()
     *
     * @var string|null
     */
    public $dayCss;

    /**
     * @Assert\Length(max=100000)
     * @Css()
     *
     * @var string|null
     */
    public $nightCss;

    public $appendToDefaultStyle = true;

    /**
     * @Assert\Length(max=300)
     *
     * @var string|null
     */
    public $comment;

    public function __construct(User $author) {
        // needed for UniqueEntity validator to work
        $this->author = $author;
    }

    public static function createFromTheme(Theme $theme): self {
        $self = new self($theme->getAuthor());
        $self->name = $theme->getName();
        $self->commonCss = $theme->getLatestRevision()->getCommonCss();
        $self->dayCss = $theme->getLatestRevision()->getDayCss();
        $self->nightCss = $theme->getLatestRevision()->getNightCss();
        $self->appendToDefaultStyle = $theme->getLatestRevision()->appendToDefaultStyle();
        $self->entityId = $theme->getId();

        return $self;
    }

    public function toTheme(): Theme {
        return new Theme(
            $this->name,
            $this->author,
            $this->commonCss,
            $this->dayCss,
            $this->nightCss,
            $this->appendToDefaultStyle,
            $this->comment
        );
    }

    public function updateTheme(Theme $theme) {
        $theme->setName($this->name);

        $revision = $theme->getLatestRevision();

        if (
            $this->commonCss !== $revision->getCommonCss() ||
            $this->dayCss !== $revision->getDayCss() ||
            $this->nightCss !== $revision->getNightCss() ||
            $this->appendToDefaultStyle !== $revision->appendToDefaultStyle()
        ) {
            $revision = new ThemeRevision(
                $theme,
                $this->commonCss,
                $this->dayCss,
                $this->nightCss,
                $this->appendToDefaultStyle,
                $this->comment
            );

            $theme->addRevision($revision);
        }
    }

    /**
     * The ID of the entity, if any, this DTO was constructed from.
     *
     * @return Uuid|null
     */
    public function getEntityId() {
        return $this->entityId;
    }
}
