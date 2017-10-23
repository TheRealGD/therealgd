<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\ForumBan;

class ForumBanRepository extends EntityRepository {
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
            ->setParameter('now', new \DateTime(), 'datetimetz');

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
