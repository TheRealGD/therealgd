<?php

namespace App\Validator\Constraints;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Replacement for {@link UniqueEntity} that works with DTOs.
 *
 * For this to work when editing something, the DTO must hold the ID of the
 * entity being edited, and the ID mapped using `$idFields`.
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class Unique extends Constraint {
    const NOT_UNIQUE_ERROR = 'eec1b008-c55b-4d91-b5ad-f0b201eb8ada';

    protected static $errorNames = [
        self::NOT_UNIQUE_ERROR => 'NOT_UNIQUE_ERROR',
    ];

    public $message = 'This value is already used.';

    public $entityClass;

    /**
     * DTO -> entity field mapping.
     *
     * @var string[]
     */
    public $fields;

    /**
     * DTO -> entity ID field mapping.
     *
     * @var string[]|null
     */
    public $idFields;

    public $errorPath;

    /**
     * {@inheritdoc}
     */
    public function __construct($options = null) {
        parent::__construct($options);

        $fields = $options['fields'] ?? $options['value'];

        if (!is_array($fields) && !is_string($fields)) {
            throw new UnexpectedTypeException($fields, 'array or string');
        }

        $fields = (array) $fields;

        if (count($fields) === 0) {
            throw new InvalidOptionsException(
                'fields option must have at least one field',
                ['fields']
            );
        }

        if (!strlen($options['entityClass'])) {
            throw new InvalidOptionsException('Bad entity class', ['entityClass']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions() {
        return ['fields', 'entityClass'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption() {
        return 'fields';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets() {
        return [Constraint::CLASS_CONSTRAINT];
    }
}
