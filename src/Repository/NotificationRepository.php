<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Notification::class);
    }

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
