<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\Notification;
use Raddit\AppBundle\Entity\User;

class NotificationRepository extends EntityRepository {
    /**
     * @param User $user
     * @param int  $page
     *
     * @return Pagerfanta
     */
    public function findNotificationsInInbox(User $user, int $page) {
        $qb = $this->createQueryBuilder('n')
            ->where('n.user = ?1')
            ->setParameter(1, $user)
            ->orderBy('n.id', 'DESC');

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage(25);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @param User $user
     */
    public function clearInbox(User $user) {
        $this->getEntityManager()->createQueryBuilder()
            ->delete(Notification::class, 'n')
            ->where('n.user = ?1')
            ->setParameter(1, $user)
            ->getQuery()
            ->execute();
    }
}
