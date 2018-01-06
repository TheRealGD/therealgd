<?php

namespace App\Form\Model;

use App\Entity\User;
use App\Entity\UserBlock;
use Symfony\Component\Validator\Constraints as Assert;

class UserBlockData {
    /**
     * @Assert\Length(max=100)
     *
     * @var string|null
     */
    private $comment;

    public function toBlock(User $blocker, User $blocked): UserBlock {
        return new UserBlock($blocker, $blocked, $this->comment);
    }

    public function getComment() {
        return $this->comment;
    }

    public function setComment($comment) {
        $this->comment = $comment;
    }
}
