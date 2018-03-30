<?php

namespace App\Utils;

use App\Entity\User;

// Class for various permissions verification actions
final class PermissionsChecker {
    public static function isAdmin(User $user = null):bool {
        return !is_null($user) && $user->isAdmin();
    }
}
