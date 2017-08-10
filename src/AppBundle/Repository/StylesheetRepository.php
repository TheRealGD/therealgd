<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\Stylesheet;

class StylesheetRepository extends EntityRepository {
    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|Stylesheet[]
     */
    public function findAllPaginated(int $page, int $maxPerPage = 25) {
        $criteria = Criteria::create()->orderBy(['timestamp' => 'DESC']);

        $stylesheets = new Pagerfanta(new DoctrineSelectableAdapter($this, $criteria));
        $stylesheets->setMaxPerPage($maxPerPage);
        $stylesheets->setCurrentPage($page);

        return $stylesheets;
    }
}
