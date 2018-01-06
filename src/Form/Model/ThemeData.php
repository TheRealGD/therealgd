<?php

namespace App\Form\Model;

use App\Entity\Theme;
use App\Entity\ThemeRevision;
use App\Entity\User;
use App\Validator\Constraints\Css;
use App\Validator\Constraints\Unique;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique({"author", "name"}, idFields={"entityId": "id"},
 *     entityClass="App\Entity\Theme", errorPath="name",
 *     message="That name is already taken.", groups={"settings"})
 */
class ThemeData {
    /**
     * @var Uuid|null
     */
    private $entityId;

    public $author;

    /**
     * @Assert\NotBlank(groups={"settings"})
     * @Assert\Length(max=50, groups={"settings"})
     *
     * @var string|null
     */
    public $name;

    /**
     * @Assert\Expression("value || this.dayCss || this.nightCss",
     *     message="At least one CSS field must be filled.", groups={"css"})
     * @Assert\Length(max=100000, groups={"css"})
     * @Css(groups={"css"})
     *
     * @var string|null
     */
    public $commonCss;

    /**
     * @Assert\Length(max=100000, groups={"css"})
     * @Css(groups={"css"})
     *
     * @var string|null
     */
    public $dayCss;

    /**
     * @Assert\Length(max=100000, groups={"css"})
     * @Css(groups={"css"})
     *
     * @var string|null
     */
    public $nightCss;

    public $appendToDefaultStyle = true;

    /**
     * @Assert\Length(max=300, groups={"css"})
     *
     * @var string|null
     */
    public $comment;

    /**
     * @Assert\Expression("value == null or value.getParentCount() < 3",
     *     message="That theme cannot be extended.", groups={"css"})
     *
     * @var ThemeRevision|null
     */
    public $parent;

    public function __construct(User $author) {
        // needed for UniqueEntity validator to work
        $this->author = $author;
    }

    public static function createFromTheme(Theme $theme): self {
        $self = new self($theme->getAuthor());
        $self->name = $theme->getName();
        $self->entityId = $theme->getId();

        $revision = $theme->getLatestRevision();

        if ($revision) {
            $self->commonCss = $revision->getCommonCss();
            $self->dayCss = $revision->getDayCss();
            $self->nightCss = $revision->getNightCss();
            $self->appendToDefaultStyle = $revision->appendToDefaultStyle();
            $self->parent = $revision->getParent();
        }

        return $self;
    }

    public function toTheme(): Theme {
        return new Theme($this->name, $this->author);
    }

    public function updateTheme(Theme $theme) {
        $theme->setName($this->name);

        $revision = $theme->getLatestRevision();

        if (
            !$revision ||
            $this->commonCss !== $revision->getCommonCss() ||
            $this->dayCss !== $revision->getDayCss() ||
            $this->nightCss !== $revision->getNightCss() ||
            $this->appendToDefaultStyle !== $revision->appendToDefaultStyle() ||
            $this->parent !== $revision->getParent()
        ) {
            $revision = new ThemeRevision(
                $theme,
                $this->commonCss,
                $this->dayCss,
                $this->nightCss,
                $this->appendToDefaultStyle,
                $this->comment,
                $this->parent
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
