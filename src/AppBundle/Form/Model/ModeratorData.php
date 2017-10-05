<?php

namespace Raddit\AppBundle\Form\Model;

use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Moderator;
use Raddit\AppBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class ModeratorData {
    /**
     * @Assert\NotBlank()
     *
     * @var User|null
     */
    private $user;

    public function toModerator(Forum $forum): Moderator {
        return new Moderator($forum, $this->user);
    }

    /**
     * @return User|null
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(User $user) {
        $this->user = $user;
    }
}
