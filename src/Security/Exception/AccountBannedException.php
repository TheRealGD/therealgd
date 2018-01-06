<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountBannedException extends AccountStatusException {
    /**
     * {@inheritdoc}
     */
    public function getMessageKey() {
        return 'Your account has been banned.';
    }
}
