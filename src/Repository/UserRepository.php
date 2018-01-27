<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Submission;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @method User|null findOneByUsername(string|string[] $username)
 * @method User|null findOneByNormalizedUsername(string|string[] $normalizedUsername)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface {
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        ManagerRegistry $registry,
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack
    ) {
        parent::__construct($registry, User::class);

        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username) {
        if ($username === null) {
            return null;
        }

        return $this->findOneByNormalizedUsername(User::normalizeUsername($username));
    }

    public function findOneOrRedirectToCanonical(?string $username, string $param): ?User {
        $user = $this->loadUserByUsername($username);

        if ($user && $user->getUsername() !== $username) {
            $request = $this->requestStack->getCurrentRequest();

            if (
                !$request ||
                $this->requestStack->getParentRequest() ||
                !$request->isMethodCacheable()
            ) {
                return $user;
            }

            $route = $request->attributes->get('_route');
            $params = $request->attributes->get('_route_params', []);
            $params[$param] = $user->getUsername();

            throw new HttpException(302, 'Redirecting to canonical', null, [
                'Location' => $this->urlGenerator->generate($route, $params),
            ]);
        }

        return $user;
    }

    /**
     * @param string $email
     *
     * @return User[]|Collection
     */
    public function lookUpByEmail(string $email) {
        // Normalization of email address is prone to change, so look them up
        // by both canonical and normalized variations just in case.
        return $this->createQueryBuilder('u')
            ->where('u.email = ?1')
            ->orWhere('u.normalizedEmail = ?2')
            ->setParameter(1, $email)
            ->setParameter(2, User::normalizeEmail($email))
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

    /**
     * @param int      $page
     * @param Criteria $criteria
     *
     * @return User[]|Pagerfanta
     */
    public function findPaginated(int $page, Criteria $criteria) {
        $pager = new Pagerfanta(new DoctrineSelectableAdapter($this, $criteria));
        $pager->setMaxPerPage(25);
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function findIpsUsedByUser(User $user): \Traversable {
        $sql = 'SELECT DISTINCT ip FROM ('.
            'SELECT ip FROM submissions WHERE user_id = :id AND ip IS NOT NULL UNION ALL '.
            'SELECT ip FROM comments WHERE user_id = :id AND ip IS NOT NULL UNION ALL '.
            'SELECT ip FROM submission_votes WHERE user_id = :id AND ip IS NOT NULL UNION ALL '.
            'SELECT ip FROM comment_votes WHERE user_id = :id AND ip IS NOT NULL UNION ALL '.
            'SELECT ip FROM message_threads WHERE sender_id = :id AND ip IS NOT NULL UNION ALL '.
            'SELECT ip FROM message_replies WHERE sender_id = :id AND ip IS NOT NULL'.
        ') q';

        $sth = $this->_em->getConnection()->prepare($sql);
        $sth->bindValue(':id', $user->getId());
        $sth->execute();

        while ($ip = $sth->fetchColumn()) {
            yield $ip;
        }
    }
}
