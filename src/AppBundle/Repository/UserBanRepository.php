<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\UserBan;

class UserBanRepository extends EntityRepository {
    /**
     * @param int  $page
     * @param int  $maxPerPage
     *
     * @return Pagerfanta|UserBan[]
     */
    public function findActiveBans(int $page, int $maxPerPage = 25) {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin(UserBan::class, 'b',
                'WITH', 'm.user = b.user AND '.
                        'm.timestamp < b.timestamp'
            )
            ->where('b.timestamp IS NULL')
            ->andWhere('m.banned = TRUE')
            ->andWhere('m.expiresAt IS NULL OR m.expiresAt >= :now')
            ->orderBy('m.timestamp', 'DESC')
            ->setParameter('now', new \DateTime(), Type::DATETIMETZ);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
