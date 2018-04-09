<?php

namespace App\Repository;

use App\Entity\RateLimit;
use App\Entity\UserGroup;
use App\Entity\Forum;
use App\Entity\User;
use App\Entity\Submission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @method RateLimit|null findOne(Forum $forum, UserGroup $group)
 */
class RateLimitRepository extends ServiceEntityRepository {
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    private $nautBot;

    public function __construct(
        ManagerRegistry $registry,
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack
    ) {
        parent::__construct($registry, RateLimit::class);

        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->nautBot = $this->getEntityManager()->find("App\\Entity\\User", 0);
    }

    public function findOneOrRedirectToCanonical(Forum $forum, UserGroup $group): ?RateLimit {
        $rate = $this->createQueryBuilder('rl')
            ->where('rl.group = :group')
            ->andWhere('rl.forum = :forum')
            ->setParameter('group', $group)
            ->setParameter('forum', $forum)
            ->getQuery()
            ->execute();

        if ($rate) {
            $request = $this->requestStack->getCurrentRequest();

            if (
                !$request ||
                $this->requestStack->getParentRequest() ||
                !$request->isMethodCacheable()
            ) {
                return $rate;
            }

            $route = $request->attributes->get('_route');
            $params = $request->attributes->get('_route_params', []);
            $params[$param] = $group->getName();

            throw new HttpException(302, 'Redirecting to canonical', null, [
                'Location' => $this->urlGenerator->generate($route, $params),
            ]);
        }

        return $group;
    }

    public function getRatesGroups(Forum $forum) {
        return $this->createQueryBuilder('rl')
            ->select('IDENTITY(rl.group)')
            ->where('rl.forum = :forum')
            ->setParameter('forum', $forum)
            ->getQuery()
            ->execute();
    }

    public function getRates(Forum $forum) {
        return $this->createQueryBuilder('rl')
            ->where('rl.forum = :forum')
            ->setParameter('forum', $forum)
            ->getQuery()
            ->execute();
    }

    public function getRateLimit(UserGroup $group = null, Forum $forum) {
        if (is_null($group)) {
            return false;
        }
        $rate = $this->createQueryBuilder('rl')
            ->where('rl.group = :group')
            ->andWhere('rl.forum = :forum')
            ->setParameter('group', $group)
            ->setParameter('forum', $forum)
            ->getQuery()
            ->execute();
        if (is_null($rate) || count($rate) <= 0) {
            return false;
        }
        return $rate[0];
    }

    /**
     * @param int      $page
     * @param Criteria $criteria
     *
     * @return RateLimit[]|Pagerfanta
     */
    public function findPaginated($forum, int $page) {
        $qb = $this->createQueryBuilder('rl');
        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage(25);
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function verifyPost(User $user, Forum $forum, Submission $new, Submission $old = null) {
        $group = $user->getGroup();
        $rate = $this->getRateLimit($group, $forum);
        if (is_null($group) || is_null($old) || $rate === false) {
echo 'failing first check';die;
            return false;
        }
        $lastPostTime = $this->getPostTimeStamp($old);
        $newPostTime = $this->getPostTimeStamp($new);
        $earliestAllowed = $lastPostTime + ($rate->getRate() * 60 * 60);
        if ($newPostTime < $earliestAllowed) {
            return $this->rateLimitViolation($new, $old, $newPostTime - $lastPostTime);
        }
        return false;
    }

    public function rateLimitViolation(Submission $new, Submission $old, int $delta) {
        $break = "  \r";
        $user = $new->getUser();
        $group = $user->getGroup();
        $forum = $new->getForum();

        $title = $user->getGroup()->getName() . ' Submission Rule Violation';

        $content = '';
        $content .= 'User: [' . $user->getUsername() . '](/user/' . $user->getUsername() . ')' . $break;
        $content .= 'Group: ' . $group->getName() . $break;
        $content .= 'New Post: [' . $new->getTitle() . '](/f/' . $forum->getName() . '/' . $new->getId() . ')' . $break;
        $content .= 'Old Post: [' . $old->getTitle() . '](/f/' . $forum->getName() . '/' . $old->getId() . ')' . $break;
        $content .= 'Time between posts: ' . round($delta / 60 / 60, 2) . ' hour(s)' .  $break;
        $content .= 'Group\'s limit: 1 post every ' . $this->getRateLimit($group, $forum)->getRate() . ' hour(s)';
        return new Submission(
            $title,
            null,
            $content,
            $forum,
            $this->nautBot,
            null,
            false,
            true
        );
    }

    private function getPostTimeStamp(Submission $post) {
        return strToTime($post->getTimestamp()->format('c'));
    }

}
