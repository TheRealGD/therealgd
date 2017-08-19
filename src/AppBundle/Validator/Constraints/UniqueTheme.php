<?php

namespace Raddit\AppBundle\Validator\Constraints;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;

/**
 * Temporary "hack" because Symfony's UniqueEntity validator doesn't support
 * DTOs.
 *
 * @Annotation
 * @Target("CLASS")
 */
class UniqueTheme extends Constraint {
    const NOT_UNIQUE_ERROR = '92911ee5-c9df-404a-84d5-07c0c7c7ddfb';

    protected static $errorNames = [
        self::NOT_UNIQUE_ERROR => 'NOT_UNIQUE_ERROR',
    ];

    public $message = 'That name is already taken.';

    /**
     * {@inheritdoc}
     */
    public function getTargets() {
        return [self::CLASS_CONSTRAINT];
    }
}
