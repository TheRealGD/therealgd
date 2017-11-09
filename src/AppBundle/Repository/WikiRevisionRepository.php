<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class WikiRevisionRepository extends EntityRepository {
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
