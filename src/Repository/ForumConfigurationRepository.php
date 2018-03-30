<?php

namespace App\Repository;

use App\Entity\ForumConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ForumConfigurationRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, ForumConfiguration::class);
    }

    public function findSitewide() {
        $config = $this->createQueryBuilder('fc')
            ->where('fc.forumId is NULL')
            ->getQuery()
            ->execute();

        if(count($config) == 0) {
            return new ForumConfiguration(null);
        }

        return $config[0];
    }
}
