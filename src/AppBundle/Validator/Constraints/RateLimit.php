<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * Rate limit by user.
 *
 * @Annotation
 * @Target("CLASS")
 */
class RateLimit extends Constraint {
    const RATE_LIMITED_ERROR = 'bf95a6b8-f86d-4c9c-80ba-db0f8630fb27';

    protected static $errorNames = [
        self::RATE_LIMITED_ERROR => 'RATE_LIMITED_ERROR',
    ];

    public $entityClass;

    public $message = 'You cannot post more. Wait a while before trying again.';

    public $max;

    public $timestampField = 'timestamp';

    public $userField = 'user';

    /**
     * {@link strtotime()} compatible date string.
     *
     * @var string
     */
    public $period;

    /**
     * {@inheritdoc}
     */
    public function __construct($options = null) {
        parent::__construct($options);

        $time = new \DateTime();
        $altered = @(clone $time)->modify($options['period']);

        if ($altered === false) {
            throw new ConstraintDefinitionException(
                '"period" must be a date string accepted by \DateTime::modify()'
            );
        }

        if ($time == $altered) {
            throw new ConstraintDefinitionException(
                'The period specifies does not alter a \DateTime object'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions() {
        return ['max', 'period'];
    }

    public function getTargets() {
        return Constraint::CLASS_CONSTRAINT;
    }
}
