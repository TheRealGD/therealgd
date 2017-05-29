<?php

namespace Raddit\Tests\AppBundle\Entity;

use PHPUnit\Framework\TestCase;
use Raddit\AppBundle\Entity\User;

class UserTest extends TestCase {
    /**
     * @dataProvider nonCanonicalUsernameProvider
     *
     * @param string $expected
     * @param string $input
     */
    public function testCanCanonicalizeUsername($expected, $input) {
        $this->assertEquals($expected, User::canonicalizeUsername($input));
    }

    /**
     * @dataProvider nonCanonicalEmailAddressProvider
     *
     * @param string $expected
     * @param string $input
     */
    public function testCanCanonicalizeEmail($expected, $input) {
        $this->assertEquals($expected, User::canonicalizeEmail($input));
    }

    /**
     * @dataProvider invalidEmailAddressProvider
     * @expectedException \InvalidArgumentException
     *
     * @param string $input
     */
    public function testCanonicalizeFailsOnInvalidEmailAddress($input) {
        User::canonicalizeEmail($input);
    }

    public function nonCanonicalUsernameProvider() {
        yield ['emma', 'Emma'];
        yield ['zach', 'zaCH'];
    }

    public function nonCanonicalEmailAddressProvider() {
        yield ['pzm87i6bhxs2vzgm@gmail.com', 'PzM87.I6bhx.S2vzGm@gmail.com'];
        yield ['ays1hbjbpluzdivl@gmail.com', 'AyS1hBjbPLuZDiVl@googlemail.com'];
        yield ['pcpanmvb@gmail.com', 'pCPaNmvB+roHYEByv@gmail.com'];
        yield ['ag9kcmxicbmkec2tldicghc@gmail.com', 'aG9KC.mxIcBMk.ec2tldiCghc+SSOkIach3@gooGLEMail.com'];
        yield ['pCPaNmvBroHYEByv@example.com', 'pCPaNmvBroHYEByv@ExaMPle.CoM'];
    }

    public function invalidEmailAddressProvider() {
        yield ['gasg7a8.'];
        yield ['foo@examplenet@example.net'];
    }
}
