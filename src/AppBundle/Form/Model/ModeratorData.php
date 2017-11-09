<?php

namespace AppBundle\Form\Model;

use AppBundle\Entity\Forum;
use AppBundle\Entity\Moderator;
use AppBundle\Entity\User;
use AppBundle\Validator\Constraints\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique({"forum", "user"}, entityClass="AppBundle\Entity\Moderator",
 *     message="That user is already a moderator.", errorPath="user")
 */
class ModeratorData {
    /**
     * @var Forum
     */
    private $forum;

    public function __construct(Forum $forum) {
        $this->forum = $forum;
    }

    /**
     * @Assert\NotBlank()
     *
     * @var User|null
     */
    private $user;

    public function toModerator(): Moderator {
        return new Moderator($this->forum, $this->user);
    }

    public function getForum(): Forum {
        return $this->forum;
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
