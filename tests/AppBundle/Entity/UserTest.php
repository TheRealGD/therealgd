<?php

namespace Tests\AppBundle\Entity;

use PHPUnit\Framework\TestCase;
use AppBundle\Entity\User;
use AppBundle\Entity\UserBan;
use AppBundle\Entity\UserBlock;

/**
 * @group time-sensitive
 */
class UserTest extends TestCase {
    public function testUsersCannotMessageUsersWhoBlockThem() {
        $sender = new User('u', 'p');

        $receiver = new User('u', 'p');
        $receiver->addBlock(new UserBlock($receiver, $sender, ''));

        $this->assertFalse($receiver->canBeMessagedBy($sender));
    }

    public function testAdminsCanMessageUsersWhoBlockThem() {
        $sender = new User('u', 'p');
        $sender->setAdmin(true);

        $receiver = new User('u', 'p');
        $receiver->addBlock(new UserBlock($receiver, $sender, ''));

        $this->assertTrue($sender->canBeMessagedBy($receiver));
    }

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

    public function testNewUserIsNotBanned() {
        $user = new User('u', 'p');

        $this->assertFalse($user->isBanned());
    }

    public function testUserBanIsEffective() {
        $user = new User('u', 'p');
        $user->addBan(new UserBan($user, 'foo', true, new User('ben', 'p')));

        $this->assertTrue($user->isBanned());
    }

    public function testExpiringUserBanIsEffective() {
        $user = new User('u', 'p');
        $expires = new \DateTime('@'.time().' +1 hour');
        $user->addBan(new UserBan($user, 'foo', true, new User('ben', 'p'), $expires));

        $this->assertTrue($user->isBanned());
    }

    public function testExpiredUserBanIsIneffective() {
        $user = new User('u', 'p');
        $expires = new \DateTime('@'.time().' +1 hour');
        $user->addBan(new UserBan($user, 'ofo', true, new User('ben', 'p'), $expires));

        sleep(7200); // 2 hours

        $this->assertFalse($user->isBanned());
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
