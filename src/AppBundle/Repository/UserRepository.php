<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Raddit\AppBundle\Entity\Submission;
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
        return $this->findOneByUsername($username);
    }
}
