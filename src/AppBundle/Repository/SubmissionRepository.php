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
     * @param string[] $forumNames
     * @param string   $sortBy
     * @param int      $page
     *
     * @return Pagerfanta|Submission[]
     */
    public function findFrontPageSubmissions(array $forumNames, string $sortBy, int $page = 1) {
        $qb = $this->findSortedQb($sortBy)
            ->join('s.forum', 'f', 'WITH', 'f.name IN (:forums)')
            ->setParameter(':forums', $forumNames);

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

    /**
     * @param QueryBuilder $qb
     */
    private function sortByHot(QueryBuilder $qb) {
        $qb->addOrderBy('s.ranking', 'DESC');
        $qb->addOrderBy('s.id', 'DESC');
    }

    /**
     * @param QueryBuilder $qb
     */
    private function sortByNewest(QueryBuilder $qb) {
        $qb->addOrderBy('s.id', 'DESC');
    }

    /**
     * @param QueryBuilder $qb
     */
    private function sortByTop(QueryBuilder $qb) {
        $qb->addSelect('COUNT(uv) - COUNT(dv) AS HIDDEN net_score')
            ->leftJoin('s.votes', 'uv', 'WITH', 'uv.upvote = true')
            ->leftJoin('s.votes', 'dv', 'WITH', 'dv.upvote = false')
            ->groupBy('s')
            ->addOrderBy('net_score', 'DESC');
    }

    /**
     * @param QueryBuilder $qb
     */
    private function sortByControversial(QueryBuilder $qb) {
        $qb->addSelect('COUNT(uv)/NULLIF(COUNT(dv), 0) AS HIDDEN controversy')
            ->leftJoin('s.votes', 'uv', 'WITH', 'uv.upvote = true')
            ->leftJoin('s.votes', 'dv', 'WITH', 'dv.upvote = false')
            ->addGroupBy('s')
            ->addOrderBy('controversy', 'ASC');
    }

    /**
     * @param QueryBuilder|\Doctrine\ORM\Query $query
     * @param int                              $page
     *
     * @return Pagerfanta
     */
    private function paginate($query, int $page) {
        $pager = new Pagerfanta(new DoctrineORMAdapter($query));
        $pager->setMaxPerPage(self::MAX_PER_PAGE);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
