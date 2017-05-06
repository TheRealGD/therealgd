<?php

namespace Raddit\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Raddit\AppBundle\Entity\Ban;

final class BanRepository extends EntityRepository {
    /**
     * @param string $ip
     *
     * @return bool
     */
    public function ipIsBanned(string $ip): bool {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException('Invalid IP address');
        }

        $sql = "SELECT COUNT(b) FROM bans b WHERE ip >>= :ip";

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
