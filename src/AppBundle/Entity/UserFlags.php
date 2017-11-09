<?php

namespace AppBundle\Entity;

/**
 * Flags that apply to a submission or comment and which describes the role of
 * the poster.
 */
final class UserFlags {
    const FLAGS = [
        self::FLAG_NONE,
        self::FLAG_MODERATOR,
        self::FLAG_ADMIN,
    ];

    const FLAG_NONE = 0;
    const FLAG_MODERATOR = 1;
    const FLAG_ADMIN = 2;
}
