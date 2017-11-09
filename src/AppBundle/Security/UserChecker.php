<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Security\Exception\AccountBannedException;
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

        if ($user->isBanned()) {
            throw new AccountBannedException();
        }
    }
}
