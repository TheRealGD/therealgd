<?php

namespace App\Repository;

use App\Entity\ForumCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ForumCategoryRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, ForumCategory::class);
    }

    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|ForumCategory[]
     */
    public function findPaginated(int $page, int $maxPerPage = 25): Pagerfanta {
        $qb = $this->createQueryBuilder('fc')
            ->orderBy('fc.normalizedName', 'ASC');

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb, false, false));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }


    // TODO: If anyone can help rewrite this to be more efficient please feel free!
    public function findCategories($isAdmin, $modForumId) {
        $qb = $this->createQueryBuilder('fc')
            ->orderBy('fc.name', 'ASC');
        $categories = $qb->getQuery()->execute();
        if (!$isAdmin && !is_null($modForumId)) {
            foreach ($categories as $category) {
                if ($category->getId() === $modForumId) {
                    $forums = $category->getForums();
                    foreach ($forums as $key => $forum) {
                        if ($forum->getId() === 0) {
                            unset($forums[$key]);
                            break;
                        }
                    }
                    break;
                }
            }
        }
        return $categories;
    }
}
