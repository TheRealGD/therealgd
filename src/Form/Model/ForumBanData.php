<?php

namespace App\Form\Model;

use App\Entity\Forum;
use App\Entity\ForumBan;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class ForumBanData {
    /**
     * @Assert\NotBlank(groups={"ban", "unban"})
     * @Assert\Length(max=300, groups={"ban", "unban"})
     *
     * @var string|null
     */
    private $reason;

    /**
     * @Assert\DateTime(groups={"ban"})
     *
     * @var string|null
     */
    private $expiryTime;

    public function toBan(Forum $forum, User $user, User $bannedBy): ForumBan {
        return new ForumBan($forum, $user, $this->reason, true, $bannedBy, $this->expiryTime);
    }

    public function toUnban(Forum $forum, User $user, User $bannedBy): ForumBan {
        return new ForumBan($forum, $user, $this->reason, false, $bannedBy);
    }

    public function getReason() {
        return $this->reason;
    }

    public function setReason($reason) {
        $this->reason = $reason;
    }

    public function getExpiryTime() {
        return $this->expiryTime;
    }

    public function setExpiryTime($expiryTime) {
        $this->expiryTime = $expiryTime;
    }
}
