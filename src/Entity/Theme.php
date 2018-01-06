<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Pagerfanta;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThemeRepository")
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
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity="ThemeRevision", mappedBy="theme", cascade={"persist"})
     * @ORM\OrderBy({"modified": "DESC"})
     *
     * @var Collection|ThemeRevision[]
     */
    private $revisions;

    public function __construct(string $name, User $author) {
        $this->id = Uuid::uuid4();
        $this->name = $name;
        $this->author = $author;
        $this->revisions = new ArrayCollection();
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

    public function getAuthor(): User {
        return $this->author;
    }

    public function getLatestRevision(): ?ThemeRevision {
        $criteria = Criteria::create()
            ->orderBy(['modified' => 'DESC', 'id' => 'ASC']);

        return $this->revisions->matching($criteria)->first() ?: null;
    }

    public function addRevision(ThemeRevision $revision) {
        if (!$this->revisions->contains($revision)) {
            $this->revisions->add($revision);
        }
    }

    public function getPaginatedRevisions(int $page, int $maxPerPage = 25) {
        $pager = new Pagerfanta(new DoctrineCollectionAdapter($this->revisions));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
