<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ForumCategoryRepository")
 * @ORM\Table(name="forum_categories")
 */
class ForumCategory {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @var string|null
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Forum", mappedBy="category")
     * @ORM\OrderBy({"normalizedName": "ASC"})
     *
     * @var Forum[]|Collection|Selectable
     */
    private $forums;

    public function __construct() {
        $this->forums = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(?string $name) {
        $this->name = $name;
    }

    /**
     * @return Collection|Selectable|Forum[]
     */
    public function getForums(): Collection {
        return $this->forums;
    }
}
