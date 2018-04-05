<?php

namespace App\Form\Model;

use App\Entity\RateLimit;
use App\Entity\UserGroup;
use App\Entity\Forum;
use App\Validator\Constraints\Unique;
use Symfony\Component\Validator\Constraints as Assert;

final class RateLimitData {
    /**
     * @var int|null
     */
    private $entityId;

    /**
     * @Assert\NotNull()
     *
     * @var UserGroup|null
     */
    private $group;

    /**
     * @Assert\NotNull()
     *
     * @var string|null
     */
    private $forum;

    /**
     * @Assert\Type(type="integer")
     * @Assert\GreaterThan(0)
     *
     * @var int|null
     */
    private $rate;

    /**
     * @var bool
     */
    private $block = false;

    public function __construct(RateLimit $rateLimit = null) {
        if ($rateLimit) {
            $this->entityId = $rateLimit->getId();
            $this->setGroup($rateLimit->getGroup());
            $this->setForum($rateLimit->getForum());
            $this->rate = $rateLimit->getRate();
            $this->block = $rateLimit->getBlock();
        }
    }

    public function toRateLimit(): RateLimit {
        return new RateLimit($this->forum, $this->group, $this->rate, $this->block);
    }

    public function updateRateLimit(RateLimit $rateLimit): void {
        $rateLimit->setGroup($this->group);
        $rateLimit->setForum($this->forum);
        $rateLimit->setRate($this->rate);
        $rateLimit->setBlock($this->block);
    }

    public function getEntityId(): ?int {
        return $this->entityId;
    }

    public function getGroup(): ?UserGroup {
        return $this->group;
    }

    public function setGroup(?UserGroup $group) {
        $this->group = $group;
    }

    public function getForum(): Forum {
        return $this->forum;
    }

    public function setForum(Forum $forum) {
        $this->forum = $forum;
    }

    public function getRate(): ?int {
        return $this->rate;
    }

    public function setRate(int $rate) {
        $this->rate = $rate;
    }

    public function getBlock(): bool {
        return $this->block;
    }

    public function setBlock(bool $block) {
        $this->block = $block;
    }
}
