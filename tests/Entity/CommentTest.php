<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Forum;
use App\Entity\Submission;
use App\Entity\User;
use App\Entity\UserFlags;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase {
    public function testNewTopLevelCommentSendsNotification() {
        $forum = $this->createMock(Forum::class);
        $submission = new Submission('a', null, null, $forum, new User('u', 'p'), null);

        $comment = new Comment('a', new User('u', 'p'), $submission);

        $this->assertCount(0, $comment->getUser()->getNotifications());
        $this->assertCount(1, $submission->getUser()->getNotifications());
    }

    public function testNewChildReplySendsNotifications() {
        $submission = $this->createMock(Submission::class);

        $parent = new Comment('a', new User('u', 'p'), $submission);
        $child = new Comment('b', new User('u', 'p'), $submission, UserFlags::FLAG_NONE, $parent);

        $this->assertCount(0, $child->getUser()->getNotifications());
        $this->assertCount(1, $parent->getUser()->getNotifications());
    }

    public function testDoesNotSendNotificationsWhenReplyingToSelf() {
        $user = new User('u', 'p');

        $submission = $this->createMock(Submission::class);

        $submission
            ->method('getUser')
            ->willReturn($user);

        $parent = new Comment('a', $user, $submission);
        new Comment('b', $user, $submission, UserFlags::FLAG_NONE, $parent);

        $this->assertCount(0, $submission->getUser()->getNotifications());
    }
}
