<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\ForumSubscription;
use Raddit\AppBundle\Entity\User;

final class ForumRepository extends EntityRepository {
    /**
     * @param int $page
     *
     * @return Forum[]|Pagerfanta
     */
    public function findForumsByPage(int $page, string $sortBy) {
        if (!preg_match('/^by_(name|title|submissions|subscribers)$/', $sortBy)) {
            throw new \InvalidArgumentException('invalid sort type');
        }

        $qb = $this->createQueryBuilder('f');

        if ($sortBy === 'subscribers') {
            $qb->addSelect('COUNT(s) AS HIDDEN subscribers')
                ->leftJoin('f.subscriptions', 's')
                ->orderBy('subscribers', 'DESC');
        } elseif ($sortBy === 'submissions') {
            $qb->addSelect('COUNT(s) AS HIDDEN submissions')
                ->leftJoin('f.submissions', 's')
                ->orderBy('submissions', 'DESC');
        } elseif ($sortBy === 'title') {
            $qb->orderBy('LOWER(f.title)', 'ASC');
        }

        $qb->addOrderBy('f.canonicalName', 'ASC')->groupBy('f.id');

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage(25);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @param User $user
     *
     * @return string[]
     */
    public function findSubscribedForumNames(User $user) {
        /** @noinspection SqlDialectInspection */
        $dql =
            'SELECT f.name FROM '.Forum::class.' f WHERE f IN ('.
                'SELECT IDENTITY(fs.forum) FROM '.ForumSubscription::class.' fs WHERE fs.user = ?1'.
            ') ORDER BY f.canonicalName ASC';

        $names = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $user)
            ->getResult();

        return array_column($names, 'name');
    }

    /**
     * Get the names of the featured forums.
     *
     * @return string[]
     */
    public function findFeaturedForumNames() {
        $names = $this->createQueryBuilder('f')
            ->select('f.name')
            ->where('f.featured = TRUE')
            ->orderBy('f.canonicalName', 'ASC')
            ->getQuery()
            ->execute();

        return array_column($names, 'name');
    }

    /**
     * @param string|null $name
     *
     * @return Forum|null
     */
    public function findOneByCaseInsensitiveName($name) {
        if ($name === null) {
            // for the benefit of param converters which for some reason insist
            // on calling repository methods with null parameters.
            return null;
        }

        return $this->createQueryBuilder('f')
            ->where('f.name = ?1')
            ->orWhere('f.canonicalName = ?2')
            ->setParameter(1, $name)
            ->setParameter(2, mb_strtolower($name, 'UTF-8'))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
