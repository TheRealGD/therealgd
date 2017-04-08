<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;

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
     * @param Forum|null $forum
     * @param int        $page
     * @param int        $max
     *
     * @return Submission[]
     */
    public function findHotSubmissions(Forum $forum = null, $page = 1, $max = self::MAX_PER_PAGE) {
        if ($page < 0) {
            throw new \InvalidArgumentException('page starts from 1');
        }

        $rsm = $this->createResultSetMappingBuilder('s');

        $sql =
            "SELECT $rsm FROM submissions s ".
                'LEFT JOIN submission_votes uv ON (uv.submission_id = s.id AND uv.upvote) '.
                'LEFT JOIN submission_votes dv ON (dv.submission_id = s.id AND NOT dv.upvote) '.
                ($forum ? 'WHERE s.forum_id = :forum ' : '').
                'GROUP BY s.id '.
                'ORDER BY EXTRACT(EPOCH FROM s.timestamp) + '.
                        'GREATEST(LEAST(:mul * (COUNT(uv) - COUNT(dv)), :mv), 0) DESC, '.
                    's.id DESC '.
                'LIMIT :limit OFFSET :offset';

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter(':mv', self::MAX_VISIBILITY);
        $query->setParameter(':mul', self::MULTIPLIER);
        $query->setParameter(':limit', $max);
        $query->setParameter(':offset', ($page - 1) * $max);

        if ($forum) {
            $query->setParameter(':forum', $forum->getId());
        }

        return $query->execute();
    }

    /**
     * @param string $sortType one of 'new', 'top' or 'controversial'
     *
     * @return QueryBuilder
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
