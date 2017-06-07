<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\MessageThread;
use Raddit\AppBundle\Entity\User;

class MessageThreadRepository extends EntityRepository {
    /**
     * @param User $user
     * @param int  $page
     *
     * @return MessageThread[]|Pagerfanta
     */
    public function findUserMessages(User $user, int $page = 1) {
        $qb = $this->createQueryBuilder('mt')
            ->where('mt.sender = :user')
            ->orWhere('mt.receiver = :user')
            ->orderBy('mt.id', 'DESC')
            ->setParameter(':user', $user);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage(25);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
