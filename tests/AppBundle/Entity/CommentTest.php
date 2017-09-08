<?php

namespace Raddit\Tests\AppBundle\Entity;

use PHPUnit\Framework\TestCase;
use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Entity\UserFlags;

class CommentTest extends TestCase {
    public function testNewTopLevelCommentSendsNotification() {
        $forum = $this->createMock(Forum::class);
        $submission = new Submission('a', null, null, $forum, new User(), null);

        $comment = new Comment('a', new User(), $submission);

        $this->assertCount(0, $comment->getUser()->getNotifications());
        $this->assertCount(1, $submission->getUser()->getNotifications());
    }

    public function testNewChildReplySendsNotifications() {
        $submission = $this->createMock(Submission::class);

        $parent = new Comment('a', new User(), $submission);
        $child = new Comment('b', new User(), $submission, UserFlags::FLAG_NONE, $parent);

        $this->assertCount(0, $child->getUser()->getNotifications());
        $this->assertCount(1, $parent->getUser()->getNotifications());
    }

    public function testDoesNotSendNotificationsWhenReplyingToSelf() {
        $user = new User();

        $submission = $this->createMock(Submission::class);

        $submission
            ->method('getUser')
            ->willReturn($user);

        $parent = new Comment('a', $user, $submission);
        new Comment('b', $user, $submission, UserFlags::FLAG_NONE, $parent);

        $this->assertCount(0, $submission->getUser()->getNotifications());
    }
}
