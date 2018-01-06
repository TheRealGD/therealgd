<?php

namespace App\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueValidator extends ConstraintValidator {
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager) {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint) {
        if (!is_array($value) && !is_object($value)) {
            throw new UnexpectedTypeException($value, 'array or object');
        }

        if (!$constraint instanceof Unique) {
            throw new UnexpectedTypeException($constraint, Unique::class);
        }

        $qb = $this->manager->createQueryBuilder()
            ->select('COUNT(e)')
            ->from($constraint->entityClass, 'e');

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $param = 0;

        foreach ((array) $constraint->fields as $dtoField => $entityField) {
            if (is_int($dtoField)) {
                $dtoField = $entityField;
            }

            $fieldValue = $propertyAccessor->getValue($value, $dtoField);

            $qb->andWhere($qb->expr()->eq('e.'.$entityField, '?'.++$param));
            $qb->setParameter($param, $fieldValue);
        }

        foreach ((array) $constraint->idFields as $dtoField => $entityField) {
            if (is_int($dtoField)) {
                $dtoField = $entityField;
            }

            $fieldValue = $propertyAccessor->getValue($value, $dtoField);

            if ($fieldValue !== null) {
                $qb->andWhere($qb->expr()->neq('e.'.$entityField, '?'.++$param));
                $qb->setParameter($param, $fieldValue);
            }
        }

        $count = $qb->getQuery()->getSingleScalarResult();

        if ($count > 0) {
            $this->context->buildViolation($constraint->message)
                ->setCode(Unique::NOT_UNIQUE_ERROR)
                ->atPath($constraint->errorPath)
                ->addViolation();
        }
    }
}
