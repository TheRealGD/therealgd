<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method User|null findOneByUsername(string|string[] $username)
 * @method User|null findOneByCanonicalUsername(string|string[] $canonicalUsername)
 */
class UserRepository extends EntityRepository implements UserLoaderInterface {
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username) {
        if ($username === null) {
            return null;
        }

        return $this->findOneByCanonicalUsername(User::canonicalizeUsername($username));
    }

    /**
     * @param string $email
     *
     * @return User[]|Collection
     */
    public function lookUpByEmail(string $email) {
        // Canonicalisation of email address is prone to change, so look them up
        // by both verbatim and canonical variations just in case.
        return $this->createQueryBuilder('u')
            ->where('u.email = ?1')
            ->orWhere('u.canonicalEmail = ?2')
            ->setParameter(1, $email)
            ->setParameter(2, User::canonicalizeEmail($email))
            ->getQuery()
            ->execute();
    }

    /**
     * Find the latest comments and submissions for a user, combined.
     *
     * This takes 1-3 queries to complete. If there is a better way of
     * performing this, I'm unaware of it.
     *
     * @param User $user
     * @param int  $limit
     *
     * @return array
     */
    public function findLatestContributions(User $user, int $limit = 25) {
        $sql = <<<EOSQL
SELECT JSON_AGG(id) AS ids, type FROM (
        SELECT id, timestamp, 'comment'::TEXT AS type FROM comments WHERE user_id = :user_id
    UNION ALL
        SELECT id, timestamp, 'submission'::TEXT AS type FROM submissions WHERE user_id = :user_id
    ORDER BY timestamp DESC
    LIMIT :limit
) q
GROUP BY type
EOSQL;

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('ids', 'ids', 'json_array'); // not really scalar
        $rsm->addIndexByScalar('type');

        $contributions = $this->_em->createNativeQuery($sql, $rsm)
            ->setParameter(':user_id', $user->getId())
            ->setParameter(':limit', $limit, 'integer')
            ->execute();

        if (!empty($contributions['comment']['ids'])) {
            $comments = $this->_em->createQueryBuilder()
                ->select('c AS comment')
                ->addSelect('c.timestamp AS timestamp')
                ->addSelect("'comment' AS type")
                ->from(Comment::class, 'c')
                ->where('c.id IN (?1)')
                ->getQuery()
                ->setParameter(1, $contributions['comment']['ids'])
                ->execute();
        }

        if (!empty($contributions['submission']['ids'])) {
            $submissions = $this->_em->createQueryBuilder()
                ->select('s AS submission')
                ->addSelect('s.timestamp AS timestamp')
                ->addSelect("'submission' AS type")
                ->from(Submission::class, 's')
                ->where('s.id IN (?1)')
                ->getQuery()
                ->setParameter(1, $contributions['submission']['ids'])
                ->execute();
        }

        $combined = array_merge($comments ?? [], $submissions ?? []);

        usort($combined, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return $combined;
    }
}
