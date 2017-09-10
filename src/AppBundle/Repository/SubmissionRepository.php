<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Utils\PrependOrderBy;

class SubmissionRepository extends EntityRepository {
    const MAX_PER_PAGE = 20;

    /**
     * @param string[] $forums array where keys are forum IDs
     * @param string   $sortBy
     * @param int      $page
     *
     * @return Pagerfanta|Submission[]
     */
    public function findFrontPageSubmissions(array $forums, string $sortBy, int $page = 1) {
        if (isset($forums[0])) {
            // make sure $forums is id => forum_name array
            throw new \InvalidArgumentException('Keys in $forums must be IDs');
        }

        $qb = $this->findSortedQb($sortBy)
            ->where('IDENTITY(s.forum) IN (:forums)')
            ->setParameter(':forums', array_keys($forums));

        return $this->paginate($qb, $page);
    }

    /**
     * @param Forum  $forum
     * @param string $sortBy
     * @param int    $page
     *
     * @return Pagerfanta|Submission[]
     */
    public function findForumSubmissions(Forum $forum, string $sortBy, int $page = 1) {
        $qb = $this->findSortedQb($sortBy)
            ->andWhere('s.forum = :forum')
            ->setParameter('forum', $forum);

        PrependOrderBy::prepend($qb, 's.sticky', 'DESC');

        return $this->paginate($qb, $page);
    }

    /**
     * @param string $sortBy
     * @param int    $page
     *
     * @return Pagerfanta|Submission[]
     */
    public function findAllSubmissions(string $sortBy, int $page = 1) {
        return $this->paginate($this->findSortedQb($sortBy), $page);
    }

    /**
     * @param string $sortType one of 'hot', 'new', 'top' or 'controversial'
     *
     * @return QueryBuilder
     */
    public function findSortedQb($sortType) {
        $qb = $this->createQueryBuilder('s');

        switch ($sortType) {
        case 'hot':
            $this->sortByHot($qb);
            break;
        case 'new':
            $this->sortByNewest($qb);
            break;
        case 'top':
            $this->sortByTop($qb);
            break;
        case 'controversial':
            $this->sortByControversial($qb);
            break;
        default:
            throw new \InvalidArgumentException('Bad sort type');
        }

        return $qb;
    }

    private function sortByHot(QueryBuilder $qb) {
        $qb->addOrderBy('s.ranking', 'DESC');
        $qb->addOrderBy('s.id', 'DESC');
    }

    private function sortByNewest(QueryBuilder $qb) {
        $qb->addOrderBy('s.id', 'DESC');
    }

    private function sortByTop(QueryBuilder $qb) {
        $qb->addSelect('COUNT(uv) - COUNT(dv) AS HIDDEN net_score')
            ->leftJoin('s.votes', 'uv', 'WITH', 'uv.upvote = true')
            ->leftJoin('s.votes', 'dv', 'WITH', 'dv.upvote = false')
            ->groupBy('s')
            ->addOrderBy('net_score', 'DESC');
    }

    private function sortByControversial(QueryBuilder $qb) {
        $qb->addSelect('COUNT(uv)/NULLIF(COUNT(dv), 0) AS HIDDEN controversy')
            ->leftJoin('s.votes', 'uv', 'WITH', 'uv.upvote = true')
            ->leftJoin('s.votes', 'dv', 'WITH', 'dv.upvote = false')
            ->addGroupBy('s')
            ->addOrderBy('controversy', 'ASC');
    }

    private function paginate($query, int $page): Pagerfanta {
        // I don't think we need to fetch-join when joined entities aren't
        // included in the result.
        $pager = new Pagerfanta(new DoctrineORMAdapter($query, false, false));
        $pager->setMaxPerPage(self::MAX_PER_PAGE);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
