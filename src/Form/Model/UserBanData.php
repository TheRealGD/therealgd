<?php

namespace App\Form\Model;

use App\Entity\IpBan;
use App\Entity\User;
use App\Entity\UserBan;
use App\Validator\Constraints\IpWithCidr;
use Symfony\Component\Validator\Constraints as Assert;

class UserBanData {
    /**
     * @Assert\NotBlank(groups={"ban_ip"})
     * @IpWithCidr(groups={"ban_ip"})
     *
     * @var string|null
     */
    public $ip;

    /**
     * @Assert\Length(max=300, groups={"ban_user", "ban_ip"})
     * @Assert\NotBlank(groups={"ban_user", "ban_ip"})
     */
    public $reason;

    public $expiresAt;

    public function __construct(string $ip = null) {
        if ($ip === null || filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->ip = $ip;
        }
    }

    public function toUserBan(User $user, User $bannedBy, bool $ban): UserBan {
        return new UserBan($user, $this->reason, $ban, $bannedBy, $this->expiresAt);
    }

    public function toIpBan(User $user, User $bannedBy): IpBan {
        if (!$this->ip) {
            throw new \BadMethodCallException('Cannot call toIpBan() without an IP address');
        }

        return new IpBan($this->ip, $this->reason, $user, $bannedBy, $this->expiresAt);
    }
}
