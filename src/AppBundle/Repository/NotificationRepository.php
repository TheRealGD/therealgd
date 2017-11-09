<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Notification;
use AppBundle\Entity\User;

class NotificationRepository extends EntityRepository {
    /**
     * @param User     $user
     * @param int|null $max
     *
     * @return int numbers of rows cleared
     */
    public function clearInbox(User $user, int $max = null) {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->delete(Notification::class, 'n')
            ->where('n.user = ?1')
            ->setParameter(1, $user);

        if ($max) {
            $qb->andWhere('n.id <= ?2')->setParameter(2, $max);
        }

        return $qb->getQuery()->execute();
    }
}
