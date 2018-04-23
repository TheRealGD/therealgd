<?php

namespace App\Repository;

use App\Entity\Forum;
use App\Entity\User;
use App\Entity\Submission;
use App\Utils\PrependOrderBy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class SubmissionRepository extends ServiceEntityRepository {
    const MAX_PER_PAGE = 25;

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Submission::class);
    }

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
        
        $submissions = $this->paginate($qb, $page);

        $this->hydrateAssociations($submissions);

        return $submissions;
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

        if ($sortBy === 'hot') {
            PrependOrderBy::prepend($qb, 's.sticky', 'DESC');
        }

        $submissions = $this->paginate($qb, $page);

        $this->hydrateAssociations($submissions);

        return $submissions;
    }

    /**
     * @param Forum  $forum
     * @param string $sortBy
     * @param int    $page
     *
     * @return Pagerfanta|Submission[]
     */
    public function findModForumSubmissions(Forum $forum, string $sortBy, int $page = 1) {
        $qb = $this->findSortedQb($sortBy, true)
            ->andWhere('s.forum = :forum')
            ->andWhere('s.modThread = true')
            ->setParameter('forum', $forum);

        if ($sortBy === 'hot') {
            PrependOrderBy::prepend($qb, 's.sticky', 'DESC');
        }

        $submissions = $this->paginate($qb, $page);

        $this->hydrateAssociations($submissions);

        return $submissions;
    }

    /**
     * @param string $sortBy
     * @param int    $page
     *
     * @return Pagerfanta|Submission[]
     */
    public function findAllSubmissions(string $sortBy, int $page = 1, $admin = false) {
        $qb = $this->findSortedQb($sortBy);
        if (!$admin) {
            $qb->andWhere('s.forum > 0');
        }
        $q = $qb->getQuery()->useQueryCache(true)->useResultCache(true);
        $submissions = $this->paginate($q, $page);

        $this->hydrateAssociations($submissions);

        return $submissions;
    }

    /**
     * @param string $sortType one of 'hot' or 'new'
     * @param bool $isAdmin to show/hide mod only threads
     *
     * @return QueryBuilder
     */
    public function findSortedQb($sortType, ?bool $isMod = false) {
        $qb = $this->createQueryBuilder('s');

        switch ($sortType) {
        case 'hot':
            $this->sortByHot($qb);
            break;
        case 'new':
            $this->sortByNewest($qb);
            break;
        case 'top':
            throw new \InvalidArgumentException('Sorting by "top" is no longer supported');
        case 'controversial':
            throw new \InvalidArgumentException('Sorting by "controversial" is no longer supported');
        default:
            throw new \InvalidArgumentException('Bad sort type');
        }

        // This hides the mod only comments from being viewed
        if (!$isMod) {
            $qb->andWhere('s.modThread = false');
        }
        return $qb;
    }

    public function getLastPostByUser(User $user, Forum $forum) {
        $qb = $this->createQueryBuilder('s');
        $post = $qb
          ->where('s.user = :user')
          ->andWhere('s.forum = :forum')
          ->setParameter('user', $user)
          ->setParameter('forum', $forum)
          ->addOrderBy('s.id', 'desc')
          ->getQuery()
          ->execute();
        if (is_null($post) || count($post) <= 0) {
            return null;
        }
        return $post[0];
    }

    public function hydrateAssociations($submissions) {
        if ($submissions instanceof \Traversable) {
            $submissions = iterator_to_array($submissions);
        } elseif (!is_array($submissions)) {
            throw new \InvalidArgumentException('$submissions must be iterable');
        }

        $this->_em->createQueryBuilder()
            ->select('PARTIAL s.{id}')
            ->addSelect('u')
            ->addSelect('f')
            ->from(Submission::class, 's')
            ->join('s.user', 'u')
            ->join('s.forum', 'f')
            ->where('s IN (?1)')
            ->setParameter(1, $submissions)
            ->getQuery()
            ->getResult();

        $this->_em->createQueryBuilder()
            ->select('PARTIAL s.{id}')
            ->addSelect('sv')
            ->from(Submission::class, 's')
            ->leftJoin('s.votes', 'sv')
            ->where('s IN (?1)')
            ->setParameter(1, $submissions)
            ->getQuery()
            ->getResult();

        $this->_em->createQueryBuilder()
            ->select('PARTIAL s.{id}')
            ->addSelect('PARTIAL c.{id}')
            ->from(Submission::class, 's')
            ->leftJoin('s.comments', 'c')
            ->where('s IN (?1)')
            ->setParameter(1, $submissions)
            ->getQuery()
            ->getResult();
    }

    private function sortByHot(QueryBuilder $qb) {
        $qb->addOrderBy('s.ranking', 'DESC');
        $qb->addOrderBy('s.id', 'DESC');
    }

    private function sortByNewest(QueryBuilder $qb) {
        $qb->addOrderBy('s.id', 'DESC');
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
