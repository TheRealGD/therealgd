<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\DBAL\Query\QueryBuilder as SQLQueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder as DQLQueryBuilder;
use Raddit\AppBundle\Entity\ForumSubscription;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;

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
     *
     * @return Submission[]
     */
    public function findFrontPageSubmissions(string $sortBy) {
        if ($sortBy === 'hot') {
            return $this->findHotSubmissions();
        }

        return $this->findSortedQb($sortBy)
            ->setMaxResults(self::MAX_PER_PAGE)
            ->getQuery()
            ->execute();
    }

    /**
     * @param string $sortBy
     * @param User   $user
     *
     * @return Submission[]
     */
    public function findLoggedInFrontPageSubmissions(string $sortBy, User $user) {
        if ($sortBy === 'hot') {
            return $this->findHotSubmissions(function ($qb) use ($user) {
                $this->nativeJoinSubscribedForums($qb, $user);
            });
        }

        $qb = $this->findSortedQb($sortBy)->setMaxResults(self::MAX_PER_PAGE);
        $this->joinSubscribedForums($qb, $user);

        return $qb->getQuery()->execute();
    }

    /**
     * Finds popular ('hot') submissions.
     *
     * The popularity of a submission is calculated using roughly the following
     * formulas:
     *
     *     net_score = upvotes - downvotes
     *     advantage = max(min(multiplier * net_score, MAX_VISIBILITY), 0)
     *     popularity = unix_time + advantage
     *
     * This ensures that posts with high net scores can rank over newer posts
     * for a maximum duration of `MAX_VISIBILITY`, depending on the multiplier
     * and score, while unpopular posts will give way to newer posts.
     *
     * By passing a callable to this method, the query can be modified as
     * needed through the `\Doctrine\DBAL\Query\QueryBuilder` object passed to
     * the callback.
     *
     * @param callable|null $callback
     *
     * @return Submission[]
     */
    public function findHotSubmissions(callable $callback = null) {
        $rsm = $this->createResultSetMappingBuilder('s');
        $em = $this->getEntityManager();

        $qb = $em->getConnection()->createQueryBuilder()
            ->select($rsm->generateSelectClause())
            ->from('submissions', 's')
            ->leftJoin('s', 'submission_votes', 'uv', 's.id = uv.submission_id AND uv.upvote')
            ->leftJoin('s', 'submission_votes', 'dv', 's.id = dv.submission_id AND NOT dv.upvote')
            ->groupBy('s.id')
            ->orderBy('EXTRACT(EPOCH FROM s.timestamp) + GREATEST(LEAST(:mul * (COUNT(uv) - COUNT(dv)), :mv), 0)', 'DESC')
            ->addOrderBy('s.id', 'DESC')
            ->setParameter(':mul', self::MULTIPLIER)
            ->setParameter(':mv', self::MAX_VISIBILITY)
            ->setMaxResults(self::MAX_PER_PAGE);

        if ($callback) {
            $callback($qb);
        }

        return $em->createNativeQuery($qb->getSQL(), $rsm)->execute($qb->getParameters());
    }

    /**
     * @param DQLQueryBuilder $qb
     * @param User            $user
     */
    public function joinSubscribedForums(DQLQueryBuilder $qb, User $user) {
        /** @noinspection SqlDialectInspection */
        $qb->andWhere('s.forum IN ('.
            'SELECT IDENTITY(fs.forum) FROM '.ForumSubscription::class.' fs WHERE fs.user = :user'.
        ')');

        $qb->setParameter('user', $user);
    }

    /**
     * @param SQLQueryBuilder $qb
     * @param User            $user
     */
    public function nativeJoinSubscribedForums(SQLQueryBuilder $qb, User $user) {
        $qb->join(
            's',
            '(SELECT forum_id AS id FROM forum_subscriptions WHERE user_id = :user_id)',
            'fs',
            's.forum_id = fs.id'
        );

        $qb->setParameter(':user_id', $user->getId());
    }

    /**
     * @param string $sortType one of 'new', 'top' or 'controversial'
     *
     * @return DQLQueryBuilder
     */
    public function findSortedQb($sortType) {
        $qb = $this->createQueryBuilder('s');

        switch ($sortType) {
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
     * @param DQLQueryBuilder $qb
     */
    private function sortByNewest(DQLQueryBuilder $qb) {
        $qb->addOrderBy('s.id', 'DESC');
    }

    /**
     * @param DQLQueryBuilder $qb
     */
    private function sortByTop(DQLQueryBuilder $qb) {
        $qb->addSelect('COUNT(uv) - COUNT(dv) AS HIDDEN net_score')
            ->leftJoin('s.votes', 'uv', 'WITH', 'uv.upvote = true')
            ->leftJoin('s.votes', 'dv', 'WITH', 'dv.upvote = false')
            ->groupBy('s')
            ->addOrderBy('net_score', 'DESC');
    }

    /**
     * @param DQLQueryBuilder $qb
     */
    private function sortByControversial(DQLQueryBuilder $qb) {
        $qb->addSelect('COUNT(uv)/NULLIF(COUNT(dv), 0) AS HIDDEN controversy')
            ->leftJoin('s.votes', 'uv', 'WITH', 'uv.upvote = true')
            ->leftJoin('s.votes', 'dv', 'WITH', 'dv.upvote = false')
            ->addGroupBy('s')
            ->addOrderBy('controversy', 'ASC');
    }
}
