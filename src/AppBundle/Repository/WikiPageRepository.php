<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use AppBundle\Entity\WikiPage;

/**
 * @method WikiPage|null findOneByCanonicalPath(string $path)
 */
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

        return $this->findOneByCanonicalPath(WikiPage::canonicalizePath($path));
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
