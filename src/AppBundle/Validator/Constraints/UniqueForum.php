<?php

namespace Raddit\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target("CLASS")
 */
class UniqueForum extends Constraint {
    const NOT_UNIQUE_ERROR = '8b7e0994-0e6e-4ffb-a350-6b3294ac7985';

    protected static $errorNames = [
        self::NOT_UNIQUE_ERROR => 'NOT_UNIQUE_ERROR',
    ];

    public $message = 'A forum by that name already exists.';

    /**
     * {@inheritdoc}
     */
    public function getTargets() {
        return [self::CLASS_CONSTRAINT];
    }
}
