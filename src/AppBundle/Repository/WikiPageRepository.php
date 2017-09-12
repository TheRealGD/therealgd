<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\WikiPage;

class WikiPageRepository extends EntityRepository {
    /**
     * @param string|null $path
     *
     * @return WikiPage|null
     */
    public function findOneCaseInsensitively($path) {
        if ($path === null) {
            return null;
        }

        return $this->createQueryBuilder('wp')
            ->where('wp.path = ?1')
            ->orWhere('wp.canonicalPath = ?2')
            ->setParameter(1, $path)
            ->setParameter(2, WikiPage::canonicalizePath($path))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $page
     *
     * @return Pagerfanta|WikiPage
     */
    public function findAllPages(int $page) {
        $qb = $this->createQueryBuilder('wp')
            ->orderBy('wp.canonicalPath', 'ASC');

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage(25);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
