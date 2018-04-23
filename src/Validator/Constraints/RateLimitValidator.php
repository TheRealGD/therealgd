<?php

namespace App\Validator\Constraints;

use App\Entity\User;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RateLimitValidator extends ConstraintValidator {
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        EntityManagerInterface $manager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint) {
      
        if ($value === null) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (!$token || !$token->getUser() instanceof User) {
            return;
        }

        if (!$constraint instanceof RateLimit) {
            throw new UnexpectedTypeException($constraint, RateLimit::class);
        }

        if (!$constraint->entityClass && !is_object($value)) {
            throw new UnexpectedTypeException($value, 'object');
        }

        $class = $constraint->entityClass ?: get_class($value);

        $time = new \DateTime('@'.time());
        $diff = $time->diff((clone $time)->modify($constraint->period));
        $time->sub($diff);

        $count = $this->manager->createQueryBuilder()
            ->select('COUNT(e)')
            ->from($class, 'e')
            ->where(sprintf('e.%s = ?1', $constraint->userField))
            ->andWhere(sprintf('e.%s >= ?2', $constraint->timestampField))
            ->setParameter(1, $token->getUser())
            ->setParameter(2, $time, Type::DATETIMETZ)
            ->getQuery()
            ->getSingleScalarResult();

        if ($count >= $constraint->max) {
            $this->context->buildViolation($constraint->message)
                ->setCode(RateLimit::RATE_LIMITED_ERROR)
                ->addViolation();
        }
    }
}
