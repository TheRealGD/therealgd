<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class SubmissionRepository extends EntityRepository {
    const SORT_TYPES = ['hot', 'new', 'top', 'controversial'];
    /**
     * @param string $sortType
     *
     * @return QueryBuilder
     */
    public function findSortedQb($sortType) {
        $qb = $this->createQueryBuilder('s');

        switch ($sortType) {
        case 'hot':
            $this->sortByHottest($qb);
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
    private function sortByHottest(QueryBuilder $qb) {
        // my shitty algorithm (it probably doesn't work):
        // hotness = 1 / (time_delta * net_score / total_votes)
        $qb->addSelect('1/(DATE_DIFF(:now, s.timestamp) * (uv - dv) / NULLIF((uv + dv), 0)) AS HIDDEN hotness')
            ->leftJoin('s.votes', 'uv', 'WITH', 'uv.upvote = true')
            ->leftJoin('s.votes', 'dv', 'WITH', 'uv.upvote = false')
            ->setParameter('now', new \DateTime('@'.time()), Type::DATETIMETZ)
            ->addOrderBy('hotness', 'DESC')
            ->addOrderBy('s.id', 'DESC');
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
