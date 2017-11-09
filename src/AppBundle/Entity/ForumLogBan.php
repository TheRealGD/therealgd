<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ForumLogBan extends ForumLogEntry {
    /**
     * @ORM\ManyToOne(targetEntity="ForumBan")
     *
     * @var ForumBan
     */
    private $ban;

    public function __construct(Forum $forum, User $user, bool $wasAdmin, ForumBan $ban, \DateTime $timestamp = null) {
        $this->ban = $ban;

        parent::__construct($forum, $user, $wasAdmin, $timestamp);
    }

    public function getBan(): ForumBan {
        return $this->ban;
    }

    public function getAction(): string {
        return 'ban';
    }
}
