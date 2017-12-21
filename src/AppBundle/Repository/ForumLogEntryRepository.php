<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ForumLogEntry;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;

class ForumLogEntryRepository extends EntityRepository {
    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|ForumLogEntry[]
     */
    public function findAllPaginated(int $page, int $maxPerPage = 50): Pagerfanta {
        $criteria = Criteria::create()->orderBy(['timestamp' => 'DESC']);

        $pager = new Pagerfanta(new DoctrineSelectableAdapter($this, $criteria));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
