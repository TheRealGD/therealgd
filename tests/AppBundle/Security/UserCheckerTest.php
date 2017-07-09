<?php

namespace Raddit\Tests\AppBundle\Security;

use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Security\UserChecker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Raddit\AppBundle\Security\UserChecker
 */
class UserCheckerTest extends TestCase {
    /**
     * @doesNotPerformAssertions
     */
    public function testNonBannedUserDoesNotCauseExceptionOnAuth() {
        /** @var User|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(false, false);

        (new UserChecker())->checkPreAuth($user);
        (new UserChecker())->checkPostAuth($user);
    }

    /**
     * @expectedException \Raddit\AppBundle\Security\Exception\AccountBannedException
     */
    public function testBannedUserCausesExceptionOnPreAuth() {
        /** @var User|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(true);

        (new UserChecker())->checkPreAuth($user);
    }

    /**
     * @expectedException \Raddit\AppBundle\Security\Exception\AccountBannedException
     */
    public function testBannedUserCausesExceptionOnPostAuth() {
        /** @var User|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(true);

        (new UserChecker())->checkPostAuth($user);
    }
}
