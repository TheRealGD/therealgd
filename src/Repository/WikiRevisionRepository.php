<?php

namespace App\Repository;

use App\Entity\WikiRevision;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class WikiRevisionRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, WikiRevision::class);
    }

    public function findRecent(int $page) {
        $qb = $this->createQueryBuilder('wr')
            ->addSelect('wr')
            ->join('wr.page', 'wp')
            ->orderBy('wr.timestamp', 'DESC');

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb, false, false));
        $pager->setMaxPerPage(25);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
