<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Raddit\AppBundle\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method User|null findOneByUsername(string|string[] $username)
 */
class UserRepository extends EntityRepository implements UserLoaderInterface {
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username) {
        return $this->createQueryBuilder('u')
            ->where('u.username = ?1 OR u.canonicalUsername = ?2')
            ->setParameter(1, $username)
            ->setParameter(2, User::canonicalizeUsername($username))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $email
     *
     * @return User[]|Collection
     */
    public function lookUpByEmail(string $email) {
        return $this->createQueryBuilder('u')
            ->where('u.email = ?1 OR u.canonicalEmail = ?2')
            ->setParameter(1, $email)
            ->setParameter(2, User::canonicalizeEmail($email))
            ->getQuery()
            ->execute();
    }
}
