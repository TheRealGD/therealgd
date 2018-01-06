<?php

namespace App\DBAL\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Types\Type;

class InetType extends Type {
    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
        return 'INET';
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'inet';
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform) {
        if (!$platform instanceof PostgreSqlPlatform) {
            throw new \InvalidArgumentException('Platform must be PostgreSQL');
        }

        if ($value === null) {
            return null;
        }

        list($ip, $cidr) = array_pad(explode('/', $value), 2, null);

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException('Invalid IP address');
        }

        if ($cidr !== null) {
            if (!ctype_digit($cidr) || !is_int(+$cidr)) {
                throw new \InvalidArgumentException('CIDR must be integer');
            }

            $length = strpos($ip, ':') !== false ? 128 : 32;

            if ($cidr > $length) {
                throw new \InvalidArgumentException("CIDR must be between 0 and $length");
            }

            // TODO: check that there aren't bits to the right of mask

            return sprintf('%s/%s', $ip, $cidr);
        }

        return $ip;
    }
}
