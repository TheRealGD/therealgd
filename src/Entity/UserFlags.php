<?php

namespace App\Entity;

/**
 * Flags that apply to a submission or comment and which describes the role of
 * the poster.
 *
 * @todo just use strings as constants instead - needs db changes, etc.
 */
final class UserFlags {
    public const FLAGS = [
        self::FLAG_NONE,
        self::FLAG_MODERATOR,
        self::FLAG_ADMIN,
    ];

    public const FLAG_NONE = 0;
    public const FLAG_MODERATOR = 1;
    public const FLAG_ADMIN = 2;

    public static function toReadable(int $userFlag): ?string {
        switch ($userFlag) {
        case self::FLAG_NONE:
            return null;
        case self::FLAG_MODERATOR:
            return 'moderator';
        case self::FLAG_ADMIN:
            return 'admin';
        default:
            throw new \InvalidArgumentException();
        }
    }
}
