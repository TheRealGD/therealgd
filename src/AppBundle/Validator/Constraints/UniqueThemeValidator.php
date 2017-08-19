<?php

namespace Raddit\AppBundle\Validator\Constraints;

use Doctrine\Common\Collections\Criteria;
use Raddit\AppBundle\Form\Model\ThemeData;
use Raddit\AppBundle\Repository\ThemeRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @see UniqueTheme
 */
class UniqueThemeValidator extends ConstraintValidator {
    /**
     * @var ThemeRepository
     */
    private $repository;

    public function __construct(ThemeRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint) {
        if (!$value instanceof ThemeData) {
            throw new UnexpectedTypeException($value, ThemeData::class);
        }

        if (!$constraint instanceof UniqueTheme) {
            throw new UnexpectedTypeException($constraint, UniqueTheme::class);
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('author', $value->author))
            ->andWhere(Criteria::expr()->eq('name', $value->name))
            ->andWhere(Criteria::expr()->neq('id', $value->getEntityId()));

        if (count($this->repository->matching($criteria)) > 0) {
            $this->context->buildViolation($constraint->message)
                ->setCode(UniqueTheme::NOT_UNIQUE_ERROR)
                ->atPath('name')
                ->addViolation();
        }
    }
}
