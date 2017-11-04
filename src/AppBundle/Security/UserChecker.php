<?php

namespace Raddit\AppBundle\Security;

use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Security\Exception\AccountBannedException;
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
