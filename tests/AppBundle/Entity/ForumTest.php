<?php

namespace Raddit\Tests\AppBundle\Entity;

use PHPUnit\Framework\TestCase;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\ForumBan;
use Raddit\AppBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class ForumTest extends TestCase {
    /**
     * @dataProvider nonPrivilegedProvider
     */
    public function testRandomsAreNotModerators($nonPrivilegedUser) {
        $forum = new Forum();
        $this->assertFalse($forum->userIsModerator($nonPrivilegedUser));
    }

    public function testModeratorsAreModerators() {
        $forum = new Forum();

        $user = new User();
        $forum->addUserAsModerator($user);

        $admin = new User();
        $admin->setAdmin(true);
        $forum->addUserAsModerator($admin);

        $this->assertTrue($forum->userIsModerator($user));
        $this->assertTrue($forum->userIsModerator($admin));
    }

    public function testAdminsAreNotModeratorsWithFlag() {
        $user = new User();
        $user->setAdmin(true);

        $forum = new Forum();

        $this->assertFalse($forum->userIsModerator($user, false));
    }

    /**
     * @dataProvider nonPrivilegedProvider
     */
    public function testRandomsCanNotDeleteForum($nonPrivilegedUser) {
        $forum = new Forum();
        $this->assertFalse($forum->userCanDelete($nonPrivilegedUser));
    }

    public function testAdminCanDeleteEmptyForum() {
        $user = new User();
        $user->setAdmin(true);

        $forum = new Forum();

        $this->assertTrue($forum->userCanDelete($user));
    }

    public function testModeratorCanDeleteEmptyForum() {
        $forum = new Forum();
        $user = new User();
        $forum->addUserAsModerator($user);

        $this->assertTrue($forum->userCanDelete($user));
    }

    public function testUserIsNotBannedInNewForum() {
        $this->assertFalse((new Forum())->userIsBanned(new User()));
    }

    public function testBansWithoutExpiryTimesWork() {
        $user = new User();

        $forum = new Forum();
        $forum->addBan(new ForumBan($forum, $user, 'a', true, new User()));

        $this->assertTrue($forum->userIsBanned($user));
    }

    public function testBansWithExpiryTimesWork() {
        $user = new User();

        $forum = new Forum();
        $forum->addBan(new ForumBan($forum, $user, 'a', true, new User(), new \DateTime('+2 weeks')));

        $this->assertTrue($forum->userIsBanned($user));
    }

    public function testBansCanExpire() {
        $user = new User();

        $forum = new Forum();
        $forum->addBan(new ForumBan($forum, $user, 'a', true, new User(), new \DateTime('-2 weeks')));

        $this->assertFalse($forum->userIsBanned($user));
    }

    public function testAdminUserIsNeverBanned() {
        $user = new User();
        $user->setAdmin(true);

        $forum = new Forum();
        $forum->addBan(new ForumBan($forum, $user, 'a', true, new User()));

        $this->assertFalse($forum->userIsBanned($user));
    }

    public function testUnbansWork() {
        $user = new User();

        $forum = new Forum();
        $forum->addBan(new ForumBan($forum, $user, 'ben', true, new User()));
        $forum->addBan(new ForumBan($forum, $user, 'unben', false, new User()));

        $this->assertFalse($forum->userIsBanned($user));
    }

    public function nonPrivilegedProvider() {
        yield [null];
        yield [$this->createMock(UserInterface::class)];
        yield ['anon.'];
        yield [new User()];
    }
}
