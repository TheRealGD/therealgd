<?php

namespace Raddit\Tests\AppBundle\Entity;

use PHPUnit\Framework\TestCase;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\ForumBan;
use Raddit\AppBundle\Entity\User;

class ForumTest extends TestCase {
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
}
