<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
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
     *
     * @var Forum[]|Collection|Selectable
     */
    private $forums;

    public function __construct() {
        $this->forums = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return Collection|Selectable|Forum[]
     */
    public function getForums() {
        return $this->forums;
    }
}
