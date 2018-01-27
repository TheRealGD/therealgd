<?php

namespace App\Repository;

use App\Entity\WikiPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @method WikiPage|null findOneByNormalizedPath(string $path)
 */
class WikiPageRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, WikiPage::class);
    }

    /**
     * @param string|null $path
     *
     * @return WikiPage|null
     */
    public function findOneCaseInsensitively($path) {
        if ($path === null) {
            return null;
        }

        return $this->findOneByNormalizedPath(WikiPage::normalizePath($path));
    }

    /**
     * @param int $page
     *
     * @return Pagerfanta|WikiPage
     */
    public function findAllPages(int $page) {
        $qb = $this->createQueryBuilder('wp')
            ->orderBy('wp.normalizedPath', 'ASC');

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage(25);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
