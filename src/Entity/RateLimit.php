<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RateLimitRepository")
 * @ORM\Table(name="rate_limits", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="rate_limit_idx", columns={"group_id", "forum_id"})
 * })
 */
class RateLimit {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="UserGroup")
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity="Forum", inversedBy="rateLimits")
     */
    private $forum;

    /**
     * @ORM\Column(type="integer")
     */
    private $rate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $block = false;

    public function __construct(Forum $forum, UserGroup $group, int $rate, bool $block) {
        $this->group = $group;
        $this->forum = $forum;
        $this->rate = $rate;
        $this->block = $block;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getForum(): Forum {
        return $this->forum;
    }

    public function setForum(Forum $forum) {
        $this->forum = $forum;
    }

    public function getGroup(): UserGroup {
        return $this->group;
    }

    public function setGroup(UserGroup $group) {
        $this->group = $group;
    }

    public function getRate() {
        return $this->rate;
    }

    public function setRate($rate) {
        $this->rate = $rate;
    }

    public function getBlock() {
        return $this->block;
    }

    public function setBlock(bool $block) {
        $this->block = $block;
    }
}
