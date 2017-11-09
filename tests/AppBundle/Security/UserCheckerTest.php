<?php

namespace Tests\AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Security\UserChecker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AppBundle\Security\UserChecker
 */
class UserCheckerTest extends TestCase {
    /**
     * @doesNotPerformAssertions
     */
    public function testNonBannedUserDoesNotCauseExceptionOnAuth() {
        /** @var User|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(false, false);

        (new UserChecker())->checkPostAuth($user);
    }

    /**
     * @expectedException \AppBundle\Security\Exception\AccountBannedException
     */
    public function testBannedUserCausesExceptionOnPostAuth() {
        /** @var User|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(true);

        (new UserChecker())->checkPostAuth($user);
    }
}
