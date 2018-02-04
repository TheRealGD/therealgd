<?php

namespace App\Repository;

use App\Entity\Forum;
use App\Entity\ForumCategory;
use App\Entity\ForumSubscription;
use App\Entity\Moderator;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @method Forum|null findOneByNormalizedName(string $normalizedName)
 */
final class ForumRepository extends ServiceEntityRepository {
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
        parent::__construct($registry, Forum::class);

        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    /**
     * @param int    $page
     * @param string $sortBy one of 'name', 'title', 'submissions',
     *                       'subscribers', optionally with 'by_' prefix
     *
     * @return Pagerfanta|Forum[]
     */
    public function findForumsByPage(int $page, string $sortBy) {
        if (!preg_match('/^(?:by_)?(name|title|submissions|subscribers)$/', $sortBy, $matches)) {
            throw new \InvalidArgumentException('invalid sort type');
        }

        $qb = $this->createQueryBuilder('f');

        switch ($matches[1]) {
        case 'subscribers':
            $qb->addSelect('COUNT(s) AS HIDDEN subscribers')
                ->leftJoin('f.subscriptions', 's')
                ->orderBy('subscribers', 'DESC');
            break;
        case 'submissions':
            $qb->addSelect('COUNT(s) AS HIDDEN submissions')
                ->leftJoin('f.submissions', 's')
                ->orderBy('submissions', 'DESC');
            break;
        case 'title':
            $qb->orderBy('LOWER(f.title)', 'ASC');
            break;
        }

        $qb->addOrderBy('f.normalizedName', 'ASC')->groupBy('f.id');

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
        /* @noinspection SqlDialectInspection */
        $dql =
            'SELECT f.id, f.name FROM '.Forum::class.' f WHERE f IN ('.
                'SELECT IDENTITY(fs.forum) FROM '.ForumSubscription::class.' fs WHERE fs.user = ?1'.
            ') ORDER BY f.normalizedName ASC';

        $names = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $user)
            ->getResult();

        return array_column($names, 'name', 'id');
    }

    /**
     * Get the names of the featured forums.
     *
     * @return string[]
     */
    public function findFeaturedForumNames() {
        $names = $this->createQueryBuilder('f')
            ->select('f.id')
            ->addSelect('f.name')
            ->where('f.featured = TRUE')
            ->orderBy('f.normalizedName', 'ASC')
            ->getQuery()
            ->execute();

        return array_column($names, 'name', 'id');
    }

    /**
     * @param User $user
     *
     * @return string[]
     */
    public function findModeratedForumNames(User $user) {
        /* @noinspection SqlDialectInspection */
        $dql = 'SELECT f.id, f.name FROM '.Forum::class.' f WHERE f IN ('.
            'SELECT IDENTITY(m.forum) FROM '.Moderator::class.' m WHERE m.user = ?1'.
        ') ORDER BY f.normalizedName ASC';

        $names = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $user)
            ->getResult();

        return array_column($names, 'name', 'id');
    }

    public function findForumNames($names) {
        /* @noinspection SqlDialectInspection */
        $dql = 'SELECT f.id, f.name FROM '.Forum::class.' f '.
            'WHERE f.normalizedName IN (?1) '.
            'ORDER BY f.normalizedName ASC';

        $names = $this->_em->createQuery($dql)
            ->setParameter(1, $names)
            ->getResult();

        return array_column($names, 'name', 'id');
    }

    public function findForumsInCategory(ForumCategory $category) {
        /* @noinspection SqlDialectInspection */
        $dql = 'SELECT f.id, f.name FROM '.Forum::class.' f '.
            'WHERE f.category = :category '.
            'ORDER BY f.normalizedName ASC';

        $names = $this->_em->createQuery($dql)
            ->setParameter('category', $category)
            ->getResult();

        return array_column($names, 'name', 'id');
    }

    public function findOneByCaseInsensitiveName(?string $name): ?Forum {
        if ($name === null) {
            // for the benefit of param converters which for some reason insist
            // on calling repository methods with null parameters.
            return null;
        }

        return $this->findOneByNormalizedName(Forum::normalizeName($name));
    }

    public function findOneOrRedirectToCanonical(?string $name, string $param): ?Forum {
        $forum = $this->findOneByCaseInsensitiveName($name);

        if ($forum && $forum->getName() !== $name) {
            $request = $this->requestStack->getCurrentRequest();

            if (
                !$request ||
                $this->requestStack->getParentRequest() ||
                !$request->isMethodCacheable()
            ) {
                // no request/is sub-request/not cacheable
                return $forum;
            }

            $route = $request->attributes->get('_route');
            $params = $request->attributes->get('_route_params', []);
            $params[$param] = $forum->getName();

            throw new HttpException(302, 'Redirecting to canonical', null, [
                'Location' => $this->urlGenerator->generate($route, $params),
            ]);
        }

        return $forum;
    }
}
