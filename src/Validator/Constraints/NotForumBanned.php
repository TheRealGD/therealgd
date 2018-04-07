<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotForumBanned extends Constraint {
    public $message = 'You have been banned from this forum.';
    public $forumPath;
    public $errorPath;

    public const FORUM_BANNED_ERROR = 'eeb18913-1b45-47d6-b676-39aec0059487';

    protected static $errorNames = [
        self::FORUM_BANNED_ERROR => 'FORUM_BANNED_ERROR',
    ];

    public function getTargets(): array {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
