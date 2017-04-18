<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\ForumSubscription;
use Raddit\AppBundle\Entity\User;

final class ForumRepository extends EntityRepository {
    /**
     * @param User $user
     *
     * @return \string[]
     */
    public function findSubscribedForumNames(User $user) {
        /** @noinspection SqlDialectInspection */
        $dql =
            'SELECT f.name FROM '.Forum::class.' f WHERE f IN ('.
                'SELECT IDENTITY(fs.forum) FROM '.ForumSubscription::class.' fs WHERE fs.user = ?1'.
            ')';

        $names = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $user)
            ->getResult();

        return array_column($names, 'name');
    }
}
