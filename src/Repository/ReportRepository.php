<?php

namespace App\Repository;

use App\Entity\Report;
use App\Entity\Comment;
use App\Entity\Submission;
use App\Entity\Forum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ReportRepository extends ServiceEntityRepository {
    const MAX_PER_PAGE = 25;

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Report::class);
    }

    /**
     * Find by comment.
     */
    public function findOneByComment(Comment $comment) {
        return $this->findOneBy(array('comment' => $comment));
    }

    /**
     * Find by submission.
     */
    public function findOneBySubmission(Submission $submission) {
        return $this->findOneBy(array('submission' => $submission));
    }

    /**
     * Find reports based on mod queue for forum.
     */
    public function findForumModQueueReports(Forum $forum, $sortBy, $page) {
      $qb = $this->createQueryBuilder('s')
          ->andWhere('s.forum = :forum')
          ->andWhere('s.isResolved = :isResolved')
          ->orderBy('s.id', 'DESC')
          ->setParameter('forum', $forum)
          ->setParameter('isResolved', false);

      $reports = $this->paginate($qb, $page);

      return $reports;
    }

    /**
     * Paginate a query builder.
     */
    private function paginate($query, int $page): Pagerfanta {
        // I don't think we need to fetch-join when joined entities aren't
        // included in the result.
        $pager = new Pagerfanta(new DoctrineORMAdapter($query, false, false));
        $pager->setMaxPerPage(self::MAX_PER_PAGE);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
