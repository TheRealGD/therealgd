<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="Raddit\AppBundle\Repository\ThemeRepository")
 * @ORM\Table(name="themes", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="themes_author_name_idx", columns={"author_id", "name"})
 * })
 */
class Theme {
    /**
     * @ORM\Column(type="uuid")
     * @ORM\Id()
     *
     * @var Uuid
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $name;

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
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $appendToDefaultStyle = true;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $author;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $lastModified;

    /**
     * @param string      $name
     * @param null|string $commonCss
     * @param null|string $dayCss
     * @param null|string $nightCss
     * @param bool        $appendToDefaultStyle
     * @param User        $author
     */
    public function __construct(
        string $name,
        $commonCss,
        $dayCss,
        $nightCss,
        bool $appendToDefaultStyle,
        User $author
    ) {
        $this->id = Uuid::uuid4();
        $this->name = $name;
        $this->setCss($commonCss, $dayCss, $nightCss);
        $this->appendToDefaultStyle = $appendToDefaultStyle;
        $this->author = $author;
        $this->updateLastModified();
    }

    public function getId(): Uuid {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getCommonCss() {
        return $this->commonCss;
    }

    /**
     * @return string|null
     */
    public function getDayCss() {
        return $this->dayCss;
    }

    /**
     * @return string|null
     */
    public function getNightCss() {
        return $this->nightCss;
    }

    /**
     * @param string|null $commonCss
     * @param string|null $dayCss
     * @param string|null $nightCss
     */
    public function setCss($commonCss, $dayCss, $nightCss) {
        if (!$commonCss && !$dayCss && !$nightCss) {
            throw new \InvalidArgumentException('At least one CSS field must be filled.');
        }

        $this->commonCss = $commonCss;
        $this->dayCss = $dayCss;
        $this->nightCss = $nightCss;
    }

    public function appendToDefaultStyle(): bool {
        return $this->appendToDefaultStyle;
    }

    public function setAppendToDefaultStyle(bool $appendToDefaultStyle) {
        $this->appendToDefaultStyle = $appendToDefaultStyle;
    }

    public function getAuthor(): User {
        return $this->author;
    }

    public function getLastModified(): \DateTime {
        return $this->lastModified;
    }

    public function updateLastModified() {
        $this->lastModified = new \DateTime('@'.time());
    }
}
