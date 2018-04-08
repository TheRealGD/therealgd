<?php

namespace App\Security;

use App\Entity\User;
use App\Security\Exception\AccountBannedException;
use App\Security\Exception\AccountDisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface {
    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user) {
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user) {
        if (!$user instanceof User) {
            return;
        }

        if($user->getId() == 0) {
            throw new AccountDisabledException();
        }

        if ($user->isBanned()) {
            throw new AccountBannedException();
        }
    }
}
