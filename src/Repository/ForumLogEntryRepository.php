<?php

namespace App\Repository;

use App\Entity\ForumLogEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;

class ForumLogEntryRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, ForumLogEntry::class);
    }

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
