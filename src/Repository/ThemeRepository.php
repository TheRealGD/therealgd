<?php

namespace App\Repository;

use App\Entity\Theme;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ThemeRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Theme::class);
    }

    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Pagerfanta|Theme[]
     */
    public function findAllPaginated(int $page, int $maxPerPage = 25) {
        $qb = $this->createQueryBuilder('t')
            ->join('t.author', 'a')
            ->join('t.revisions', 'tr')
            ->orderBy('LOWER(a.username)', 'ASC')
            ->addOrderBy('LOWER(t.name)', 'ASC');

        $themes = new Pagerfanta(new DoctrineORMAdapter($qb));
        $themes->setMaxPerPage($maxPerPage);
        $themes->setCurrentPage($page);

        return $themes;
    }

    /**
     * @param string|null $username
     * @param string|null $name
     *
     * @return Theme|null
     */
    public function findOneByUsernameAndName($username, $name) {
        if ($username === null || $name === null) {
            return null;
        }

        return $this->createQueryBuilder('t')
            ->where('t.author = (SELECT u FROM '.User::class.' u WHERE u.username = :username)')
            ->andWhere('t.name = :name')
            ->setParameter('username', $username)
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
