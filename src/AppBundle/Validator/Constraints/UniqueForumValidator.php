<?php

namespace Raddit\AppBundle\Validator\Constraints;

use Doctrine\Common\Collections\Criteria;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Form\Model\ForumData;
use Raddit\AppBundle\Repository\ForumRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @see UniqueForum
 */
class UniqueForumValidator extends ConstraintValidator {
    /**
     * @var ForumRepository
     */
    private $repository;

    public function __construct(ForumRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint) {
        if (!$value instanceof ForumData) {
            throw new UnexpectedTypeException($value, ForumData::class);
        }

        if (!$constraint instanceof UniqueForum) {
            throw new UnexpectedTypeException($constraint, UniqueForum::class);
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('canonicalName', Forum::canonicalizeName($value->getName())))
            ->andWhere(Criteria::expr()->neq('id', $value->getEntityId()));

        if (count($this->repository->matching($criteria)) > 0) {
            $this->context->buildViolation($constraint->message)
                ->setCode(UniqueForum::NOT_UNIQUE_ERROR)
                ->atPath('name')
                ->addViolation();
        }
    }
}

