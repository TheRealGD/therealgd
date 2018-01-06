<?php

namespace App\Repository;

use App\Entity\IpBan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;

final class IpBanRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, IpBan::class);
    }

    public function findAllPaginated($page, $maxPerPage = 25) {
        $criteria = Criteria::create()->orderBy(['timestamp' => 'DESC']);

        $bans = new Pagerfanta(new DoctrineSelectableAdapter($this, $criteria));
        $bans->setMaxPerPage($maxPerPage);
        $bans->setCurrentPage($page);

        return $bans;
    }

    /**
     * @param string $ip
     *
     * @return bool
     */
    public function ipIsBanned(string $ip): bool {
        $count = $this->_em->getConnection()->createQueryBuilder()
            ->select('COUNT(b)')
            ->from('bans', 'b')
            ->where('ip >>= :ip')
            ->andWhere('(expiry_date IS NULL OR expiry_date >= :now)')
            ->setParameter('ip', $ip, 'inet')
            ->setParameter('now', new \DateTime(), 'datetimetz')
            ->execute()
            ->fetchColumn();

        return $count > 0;
    }
}
