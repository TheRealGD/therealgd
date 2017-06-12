<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Raddit\AppBundle\Repository\WikiPageRepository")
 * @ORM\Table(name="wiki_pages")
 */
class WikiPage {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(type="text", unique=true)
     *
     * @var string|null
     */
    private $path;

    /**
     * @ORM\Column(type="text", unique=true)
     *
     * @var string|null
     */
    private $canonicalPath;

    /**
     * @ORM\ManyToOne(targetEntity="WikiRevision", cascade={"persist"})
     *
     * @var WikiRevision|null
     */
    private $currentRevision;

    /**
     * @ORM\OneToMany(targetEntity="WikiRevision", mappedBy="page", cascade={"persist"})
     *
     * @var WikiRevision[]|Collection
     */
    private $revisions;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @var bool
     */
    private $locked = false;

    public static function canonicalizePath(string $path) {
        return strtolower(str_replace('-', '_', $path));
    }

    public function __construct() {
        $this->revisions = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @param string|null $path
     */
    public function setPath($path) {
        $this->path = $path;
        $this->canonicalPath = strlen($path) ? self::canonicalizePath($path) : null;
    }

    /**
     * @return string|null
     */
    public function getCanonicalPath() {
        return $this->canonicalPath;
    }

    /**
     * @return WikiRevision|null
     */
    public function getCurrentRevision() {
        return $this->currentRevision;
    }

    /**
     * @param WikiRevision|null $currentRevision
     */
    public function setCurrentRevision($currentRevision) {
        $this->currentRevision = $currentRevision;
    }

    /**
     * @return Collection|WikiRevision[]
     */
    public function getRevisions() {
        return $this->revisions;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool {
        return $this->locked;
    }

    /**
     * @param bool $locked
     */
    public function setLocked(bool $locked) {
        $this->locked = $locked;
    }
}
