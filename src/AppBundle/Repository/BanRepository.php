<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\Ban;

final class BanRepository extends EntityRepository {
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
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException('Invalid IP address');
        }

        $sql = 'SELECT COUNT(b) FROM bans b WHERE ip >>= :ip';

        $sth = $this->getEntityManager()->getConnection()->prepare($sql);
        $sth->execute([$ip]);

        return $sth->fetchColumn() > 0;
    }

    /**
     * @param string $ip
     *
     * @return Ban[]
     */
    public function findBansByIp(string $ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException('Invalid IP address');
        }

        $rsm = $this->createResultSetMappingBuilder('b');
        $sql = "SELECT $rsm FROM bans WHERE ip >>= :ip";

        return $this->getEntityManager()
            ->createNativeQuery($sql, $rsm)
            ->setParameter(':ip', $ip)
            ->execute();
    }
}
