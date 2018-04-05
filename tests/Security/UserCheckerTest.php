<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Security\UserChecker
 */
class UserCheckerTest extends TestCase {
    /**
     * @doesNotPerformAssertions
     */
    public function testNonBannedUserDoesNotCauseExceptionOnAuth() {
        /* @var User|MockObject $user */
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(false, false);

        (new UserChecker())->checkPostAuth($user);
    }

    /**
     * @expectedException \App\Security\Exception\AccountBannedException
     */
    public function testBannedUserCausesExceptionOnPostAuth() {
        /* @var User|MockObject $user */
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(true);

        (new UserChecker())->checkPostAuth($user);
    }
}
