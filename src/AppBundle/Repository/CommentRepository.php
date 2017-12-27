<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;

class CommentRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Comment::class);
    }

    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|Comment[]
     */
    public function findRecentPaginated(int $page, int $maxPerPage = 25) {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('softDeleted', false))
            ->orderBy(['timestamp' => 'DESC']);

        $pager = new Pagerfanta(new DoctrineSelectableAdapter($this, $criteria));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
