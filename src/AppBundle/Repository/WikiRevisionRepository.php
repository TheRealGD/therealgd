<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\WikiPage;
use Raddit\AppBundle\Entity\WikiRevision;

class WikiRevisionRepository extends EntityRepository {
    /**
     * @param WikiPage $wikiPage
     * @param int      $page
     *
     * @return Pagerfanta|WikiRevision[]
     */
    public function findRevisionsForPage(WikiPage $wikiPage, int $page) {
        $qb = $this->createQueryBuilder('wr')
            ->where('wr.page = ?1')
            ->orderBy('wr.id', 'DESC')
            ->setParameter(1, $wikiPage);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage(25);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
