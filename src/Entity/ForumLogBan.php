<?php

namespace App\Entity;

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

    public function __construct(ForumBan $ban) {
        $this->ban = $ban;

        parent::__construct($ban->getForum(), $ban->getBannedBy());
    }

    public function getBan(): ForumBan {
        return $this->ban;
    }

    public function getAction(): string {
        return 'ban';
    }
}
