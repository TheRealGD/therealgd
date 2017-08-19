<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\Theme;

class ThemeRepository extends EntityRepository {
    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|Theme[]
     */
    public function findAllPaginated(int $page, int $maxPerPage = 25) {
        $criteria = Criteria::create()->orderBy(['lastModified' => 'DESC']);

        $themes = new Pagerfanta(new DoctrineSelectableAdapter($this, $criteria));
        $themes->setMaxPerPage($maxPerPage);
        $themes->setCurrentPage($page);

        return $themes;
    }
}
