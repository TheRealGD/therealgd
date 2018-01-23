<?php

namespace App\Repository;

use App\Entity\Forum;
use App\Entity\ForumBan;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\Type;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ForumBanRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, ForumBan::class);
    }

    /**
     * Find all bans in a forum that haven't been undone and which haven't
     * expired.
     *
     * @param Forum $forum
     * @param int   $page
     * @param int   $maxPerPage
     *
     * @return Pagerfanta|ForumBan[]
     */
    public function findValidBansInForum(Forum $forum, int $page, int $maxPerPage = 25) {
        // Oh, you need to change the query? Good luck. :v)
        $qb = $this->createQueryBuilder('m')
            ->leftJoin(ForumBan::class, 'b',
                'WITH', 'm.user = b.user AND '.
                        'm.forum = b.forum AND '.
                        'm.timestamp < b.timestamp'
            )
            ->where('b.timestamp IS NULL')
            ->andWhere('m.banned = TRUE')
            ->andWhere('m.forum = :forum')
            ->andWhere('m.expiresAt IS NULL OR m.expiresAt >= :now')
            ->orderBy('m.timestamp', 'DESC')
            ->setParameter('forum', $forum)
            ->setParameter('now', new \DateTime(), Type::DATETIMETZ);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function findActiveBansByUser(User $user, int $page, int $maxPerPage = 25) {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin(ForumBan::class, 'b',
                'WITH', 'm.user = b.user AND '.
                        'm.forum = b.forum AND '.
                        'm.timestamp < b.timestamp'
            )
            ->where('b.timestamp IS NULL')
            ->andWhere('m.banned = TRUE')
            ->andWhere('m.user = :user')
            ->andWhere('m.expiresAt IS NULL OR m.expiresAt >= :now')
            ->orderBy('m.timestamp', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime(), Type::DATETIMETZ);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
