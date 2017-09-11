<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity()
 * @ORM\Table(name="theme_revisions")
 */
class ThemeRevision {
    /**
     * @ORM\Column(type="uuid")
     * @ORM\Id()
     *
     * @var Uuid
     */
    private $id;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Theme", inversedBy="revisions")
     *
     * @var Theme
     */
    private $theme;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $commonCss;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $dayCss;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $nightCss;

    /**
     * @var bool
     */
    private $appendToDefaultStyle = true;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $comment;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $modified;

    public function __construct(
        Theme $theme,
        $commonCss,
        $dayCss,
        $nightCss,
        bool $appendToDefaultStyle,
        $comment,
        \DateTime $modified = null
    ) {
        if (!$commonCss && !$dayCss && !$nightCss) {
            throw new \DomainException('At least one CSS field must be filled');
        }

        $this->id = Uuid::uuid4();
        $this->theme = $theme;
        $this->commonCss = $commonCss;
        $this->dayCss = $dayCss;
        $this->nightCss = $nightCss;
        $this->appendToDefaultStyle = $appendToDefaultStyle;
        $this->comment = $comment;
        $this->modified = $modified ?: new \DateTime('@'.time());
    }

    public function getId(): Uuid {
        return $this->id;
    }

    public function getTheme(): Theme {
        return $this->theme;
    }

    /**
     * @return null|string
     */
    public function getCommonCss() {
        return $this->commonCss;
    }

    /**
     * @return null|string
     */
    public function getDayCss() {
        return $this->dayCss;
    }

    /**
     * @return null|string
     */
    public function getNightCss() {
        return $this->nightCss;
    }

    public function appendToDefaultStyle(): bool {
        return $this->appendToDefaultStyle;
    }

    /**
     * @return string|null
     */
    public function getComment() {
        return $this->comment;
    }

    public function getModified(): \DateTime {
        return $this->modified;
    }
}
