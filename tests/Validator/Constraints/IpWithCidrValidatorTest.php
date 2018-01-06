<?php

namespace App\Tests\Validator\Constraints;

use App\Validator\Constraints\IpWithCidr;
use App\Validator\Constraints\IpWithCidrValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class IpWithCidrValidatorTest extends ConstraintValidatorTestCase {
    protected function createValidator() {
        return new IpWithCidrValidator();
    }

    /**
     * @dataProvider validValuesProvider
     */
    public function testValidValues($value) {
        $this->validator->validate($value, new IpWithCidr());

        $this->assertNoViolation();
    }

    /**
     * @dataProvider cidrLessProvider
     */
    public function testRaisesErrorWithoutCidr($value) {
        $constraint = new IpWithCidr([
            'cidrOptional' => false,
            'missingCidrMessage' => 'missingCidr',
        ]);

        $this->validator->validate($value, $constraint);

        $this->buildViolation('missingCidr')
            ->setCode(IpWithCidr::MISSING_CIDR)
            ->assertRaised();
    }

    /**
     * @dataProvider invalidIpProvider
     */
    public function testRaisesErrorOnInvalidIp($value) {
        $constraint = new IpWithCidr([
            'invalidIpMessage' => 'invalidIp',
        ]);

        $this->validator->validate($value, $constraint);

        $this->buildViolation('invalidIp')
            ->setCode(IpWithCidr::INVALID_IP)
            ->assertRaised();
    }

    /**
     * @dataProvider invalidCidrProvider
     */
    public function testRaisesErrorOnInvalidCidr($value) {
        $constraint = new IpWithCidr([
            'invalidCidrMessage' => 'invalidCidr',
        ]);

        $this->validator->validate($value, $constraint);

        $this->buildViolation('invalidCidr')
            ->setCode(IpWithCidr::INVALID_CIDR)
            ->assertRaised();
    }

    public function validValuesProvider() {
        yield ['127.0.0.1/32'];
        yield ['254.253.252.251/31'];
        yield ['192.168.4.20'];
        yield ['1312::1917/24'];
        yield ['420::69/15'];
        yield ['4:3:2::1'];
    }

    public function cidrLessProvider() {
        yield ['::1'];
        yield ['192.168.4.20'];
    }

    public function invalidIpProvider() {
        yield ['256.256.256.256/32'];
        yield ['goop::crap'];
    }

    public function invalidCidrProvider() {
        yield ['::1/129'];
        yield ['::/-128'];
        yield ['127.6.5.4/33'];
        yield ['127.0.0.1/'.PHP_INT_MAX.PHP_INT_MAX];
        yield ['127.0.0.1/crap'];
    }
}
