<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\WikiPage;

class WikiPageRepository extends EntityRepository {
    /**
     * @param int $page
     *
     * @return Pagerfanta|WikiPage
     */
    public function findAllPages(int $page) {
        $qb = $this->createQueryBuilder('wp')
            ->join('wp.currentRevision', 'wr')
            ->orderBy('LOWER(wr.title)', 'ASC');

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage(25);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
