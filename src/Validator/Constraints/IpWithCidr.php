<?php

namespace App\Validator\Constraints;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "ANNOTATION"})
 */
class IpWithCidr extends Constraint {
    const INVALID_IP = '24672f6c-5a23-4067-8566-e44c35db9556';
    const INVALID_CIDR = 'adf9db03-ccd6-43d2-8fd6-8dcc9ce9c3a1';
    const MISSING_CIDR = '07301ef6-c958-430d-952e-2969a7d9cfb9';

    protected static $errorNames = [
        self::INVALID_IP => 'INVALID_IP',
        self::INVALID_CIDR => 'INVALID_CIDR',
        self::MISSING_CIDR => 'MISSING_CIDR',
    ];

    public $cidrOptional = true;

    public $invalidIpMessage = 'The IP address is not valid.';
    public $invalidCidrMessage = 'The CIDR mask is not valid.';
    public $missingCidrMessage = 'Missing CIDR mask.';

    /**
     * {@inheritdoc}
     */
    public function getTargets() {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
