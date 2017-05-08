<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\ForumSubscription;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Utils\PrependOrderBy;

class SubmissionRepository extends EntityRepository {
    const MAX_PER_PAGE = 20;

    /**
     * The time in seconds during which an older post can have a higher rank
     * than a newer one.
     *
     * @var int
     */
    const MAX_VISIBILITY = 28800;

    /**
     * Amount to multiply the net score with.
     *
     * @todo This should be calculated based on recent site activity.
     *
     * @var int
     */
    const MULTIPLIER = 1800;

    /**
     * @param string $sortBy
     * @param int    $page
     *
     * @return Pagerfanta|Submission[]
     */
    public function findFrontPageSubmissions(string $sortBy, $page = 1) {
        $qb = $this->findSortedQb($sortBy)
            ->andWhere('s.forum IN (SELECT f FROM '.Forum::class.' f WHERE f.featured = TRUE)')
            ->setMaxResults(self::MAX_PER_PAGE);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage(self::MAX_PER_PAGE);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @param string $sortBy
     * @param User   $user
     * @param int    $page
     *
     * @return Pagerfanta|Submission[]
     */
    public function findLoggedInFrontPageSubmissions(string $sortBy, User $user, int $page = 1) {
        $qb = $this->findSortedQb($sortBy)->setMaxResults(self::MAX_PER_PAGE);
        $this->joinSubscribedForums($qb, $user);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage(self::MAX_PER_PAGE);
        $pager->setCurrentPage($page);

        return $pager;
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
            ->setParameter('forum', $forum)
            ->setMaxResults(self::MAX_PER_PAGE);

        PrependOrderBy::prepend($qb, 's.sticky', 'DESC');

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage(self::MAX_PER_PAGE);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @param QueryBuilder $qb
     * @param User         $user
     */
    public function joinSubscribedForums(QueryBuilder $qb, User $user) {
        /* @noinspection SqlDialectInspection */
        $qb->andWhere('s.forum IN ('.
            'SELECT IDENTITY(fs.forum) FROM '.ForumSubscription::class.' fs WHERE fs.user = :user'.
        ')');

        $qb->setParameter('user', $user);
    }

    public function recalculateRank(Submission $submission, int $scoreDelta) {
        if ($submission->getId() !== null) {
            $sql =
                'SELECT COUNT(uv) - COUNT(dv) '.
                'FROM submissions s '.
                'LEFT JOIN submission_votes uv ON (s.id = uv.submission_id AND uv.upvote) '.
                'LEFT JOIN submission_votes dv ON (s.id = dv.submission_id AND NOT dv.upvote) '.
                'WHERE s.id = ? '.
                'GROUP BY s.id';

            $conn = $this->getEntityManager()->getConnection();

            $netScore = $conn->fetchColumn($sql, [$submission->getId()]);
            $netScore += $scoreDelta;
        } else {
            // this score is always correct when the submission is non-persisted
            $netScore = $submission->getNetScore();
        }

        $unixTime = $submission->getTimestamp()->getTimestamp();
        $advantage = max(min(self::MULTIPLIER * $netScore, self::MAX_VISIBILITY), 0);
        $submission->setRanking($unixTime + $advantage);
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
}
