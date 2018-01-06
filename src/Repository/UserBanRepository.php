<?php

namespace App\Repository;

use App\Entity\UserBan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\Type;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class UserBanRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, UserBan::class);
    }

    /**
     * @param int $page
     * @param int $maxPerPage
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
