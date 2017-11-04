<?php

namespace Raddit\AppBundle\Form\Model;

use Raddit\AppBundle\Entity\IpBan;
use Raddit\AppBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class IpBanData {
    /**
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    public $ip;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=300)
     *
     * @var string|null
     */
    public $reason;

    /**
     * @var \DateTime|null
     */
    public $expiryDate;

    /**
     * @var User|null
     */
    public $user;

    public function toIpBan(User $bannedBy): IpBan {
        return new IpBan(
            $this->ip,
            $this->reason,
            $this->user,
            $bannedBy,
            $this->expiryDate
        );
    }
}
