<?php

namespace Raddit\AppBundle\Utils;

use Doctrine\ORM\QueryBuilder as DQLQueryBuilder;
use Doctrine\DBAL\Query\QueryBuilder as SQLQueryBuilder;

/**
 * Work around missing ability to prepend orderBy clauses in Doctrine's query
 * builder classes.
 */
final class PrependOrderBy {
    /**
     * @param DQLQueryBuilder|SQLQueryBuilder $qb
     * @param string                          $clause
     * @param string                          $order
     */
    public static function prepend($qb, string $clause, string $order = 'ASC') {
        if ($qb instanceof SQLQueryBuilder) {
            self::prependWithSqlBuilder($qb, $clause, $order);
        } elseif ($qb instanceof DQLQueryBuilder) {
            self::prependWithDqlBuilder($qb, $clause, $order);
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Parameter 1 must be %s or %s',
                DQLQueryBuilder::class,
                SQLQueryBuilder::class
            ));
        }
    }

    private static function prependWithSqlBuilder(SQLQueryBuilder $qb, string $clause, string $order) {
        $orderBy = $qb->getQueryPart('orderBy');

        $qb->orderBy($clause, $order);

        foreach ($orderBy as $clause) {
            preg_match('/^(.*) (ASC|DESC)$/', $clause, $matches);
            $qb->addOrderBy($matches[1], $matches[2]);
        }
    }

    private static function prependWithDqlBuilder(DQLQueryBuilder $qb, string $clause, string $order) {
        $orderBy = $qb->getDQLPart('orderBy');
        $qb->orderBy($clause, $order);

        foreach ($orderBy as $clause) {
            $qb->addOrderBy($clause);
        }
    }
}
