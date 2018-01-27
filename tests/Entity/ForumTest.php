<?php

namespace App\Tests\Entity;

use App\Entity\Forum;
use App\Entity\ForumBan;
use App\Entity\Moderator;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class ForumTest extends TestCase {
    /**
     * @var Forum
     */
    private $forum;

    protected function setUp() {
        $this->forum = new Forum('name', 'title', 'description', 'sidebar');
    }

    /**
     * @dataProvider nonPrivilegedProvider
     */
    public function testRandomsAreNotModerators($nonPrivilegedUser) {
        $this->assertFalse($this->forum->userIsModerator($nonPrivilegedUser));
    }

    public function testModeratorsAreModerators() {
        $user = new User('u', 'p');
        new Moderator($this->forum, $user);

        $admin = new User('u', 'p');
        $admin->setAdmin(true);
        new Moderator($this->forum, $admin);

        $this->assertTrue($this->forum->userIsModerator($user));
        $this->assertTrue($this->forum->userIsModerator($admin));
    }

    public function testAdminsAreNotModeratorsWithFlag() {
        $user = new User('u', 'p');
        $user->setAdmin(true);

        $this->assertFalse($this->forum->userIsModerator($user, false));
    }

    /**
     * @dataProvider nonPrivilegedProvider
     */
    public function testRandomsCanNotDeleteForum($nonPrivilegedUser) {
        $this->assertFalse($this->forum->userCanDelete($nonPrivilegedUser));
    }

    public function testAdminCanDeleteEmptyForum() {
        $user = new User('u', 'p');
        $user->setAdmin(true);

        $this->assertTrue($this->forum->userCanDelete($user));
    }

    public function testModeratorCanDeleteEmptyForum() {
        $user = new User('u', 'p');
        new Moderator($this->forum, $user);

        $this->assertTrue($this->forum->userCanDelete($user));
    }

    public function testUserIsNotBannedInNewForum() {
        $this->assertFalse($this->forum->userIsBanned(new User('u', 'p')));
    }

    public function testBansWithoutExpiryTimesWork() {
        $user = new User('u', 'p');

        $this->forum->addBan(new ForumBan($this->forum, $user, 'a', true, new User('u', 'p')));

        $this->assertTrue($this->forum->userIsBanned($user));
    }

    public function testBansWithExpiryTimesWork() {
        $user = new User('u', 'p');

        $this->forum->addBan(new ForumBan($this->forum, $user, 'a', true, new User('u', 'p'), new \DateTime('+2 weeks')));

        $this->assertTrue($this->forum->userIsBanned($user));
    }

    public function testBansCanExpire() {
        $user = new User('u', 'p');

        $this->forum->addBan(new ForumBan($this->forum, $user, 'a', true, new User('u', 'p'), new \DateTime('-2 weeks')));

        $this->assertFalse($this->forum->userIsBanned($user));
    }

    public function testAdminUserIsNeverBanned() {
        $user = new User('u', 'p');
        $user->setAdmin(true);

        $this->forum->addBan(new ForumBan($this->forum, $user, 'a', true, new User('u', 'p')));

        $this->assertFalse($this->forum->userIsBanned($user));
    }

    public function testUnbansWork() {
        $user = new User('u', 'p');

        $this->forum->addBan(new ForumBan($this->forum, $user, 'ben', true, new User('u', 'p')));
        $this->forum->addBan(new ForumBan($this->forum, $user, 'unben', false, new User('u', 'p')));

        $this->assertFalse($this->forum->userIsBanned($user));
    }

    public function nonPrivilegedProvider() {
        yield [null];
        yield [$this->createMock(UserInterface::class)];
        yield ['anon.'];
        yield [new User('u', 'p')];
    }
}
