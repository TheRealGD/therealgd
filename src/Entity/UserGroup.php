<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserGroupRepository")
 * @ORM\Table(name="user_group", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="user_group_name_idx", columns={"name"}),
 *     @ORM\UniqueConstraint(name="user_group_normalized_name_idx", columns={"normalized_name"})
 * })
 */
class UserGroup {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="text", unique=true)
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="text", unique=true)
     *
     * @var string
     */
    private $normalizedName;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="group")
     * @ORM\OrderBy({"normalizedUsername": "ASC"})
     *
     * @var User[]|Collection|Selectable
     */
    private $users;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $displayTitle = false;

    public function __construct(string $name, string $title) {
        $this->setName($name);
        $this->title = $title;
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int {
        // todo: replace with UUID
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
        $this->normalizedName = self::normalizeName($name);
    }

    public function getNormalizedName(): string {
        return $this->normalizedName;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    /**
     * @return Collection|Selectable|User[]
     */
    public function getUsers(): Collection {
        return $this->users;
    }

    public static function normalizeName(string $name) {
        return Forum::normalizeName($name);
    }

    public function getDisplayTitle() {
        return $this->displayTitle;
    }

    public function setDisplayTitle($displayTitle) {
        $this->displayTitle =  $displayTitle;
    }
}

