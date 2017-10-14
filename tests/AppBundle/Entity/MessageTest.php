<?php

namespace Raddit\Tests\AppBundle\Entity;

use PHPUnit\Framework\TestCase;
use Raddit\AppBundle\Entity\MessageReply;
use Raddit\AppBundle\Entity\MessageThread;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Entity\UserBlock;

class MessageTest extends TestCase {
    /**
     * @var User
     */
    private $sender;

    /**
     * @var User
     */
    private $receiver;

    protected function setUp() {
        $this->sender = new User('u', 'p');
        $this->receiver = new User('u', 'p');
    }

    public function testNewMessageThreadsSendNotifications() {
        new MessageThread($this->sender, 'b', null, $this->receiver, 'a');

        $this->assertCount(0, $this->sender->getNotifications());
        $this->assertCount(1, $this->receiver->getNotifications());
    }

    public function testNewMessageRepliesSendsNotifications() {
        $thread = $this->createMock(MessageThread::class);

        $thread
            ->method('getSender')
            ->willReturn($this->receiver);

        $thread
            ->method('getReceiver')
            ->willReturn($this->sender);

        new MessageReply($this->sender, 'c', null, $thread);
        new MessageReply($this->receiver, 'd', null, $thread);

        $this->assertCount(1, $this->receiver->getNotifications());
        $this->assertCount(1, $this->sender->getNotifications());
    }

    public function testNonParticipantsCannotAccessThread() {
        $thread = new MessageThread(new User('u', 'p'), 'b', null, new User('u', 'p'), 'a');

        $this->assertFalse($thread->userCanAccess(new User('u', 'p')));
    }

    public function testBothParticipantsCanAccessOwnThread() {
        $this->sender = new User('u', 'p');
        $this->receiver = new User('u', 'p');

        $thread = new MessageThread($this->sender, 'b', null, $this->receiver, 'a');

        $this->assertTrue($thread->userCanAccess($this->receiver));
        $this->assertTrue($thread->userCanAccess($this->sender));
    }

    public function testBothParticipantsCanReplyToThread() {
        $this->sender = new User('u', 'p');
        $this->receiver = new User('u', 'p');

        $thread = new MessageThread($this->sender, 'b', null, $this->receiver, 'a');

        $this->assertTrue($thread->userCanReply($this->receiver));
        $this->assertTrue($thread->userCanReply($this->sender));
    }

    public function testThrowsExceptionWhenStartingThreadWithBlockedUser() {
        $this->sender = new User('u', 'p');
        $this->receiver = new User('u', 'p');

        $this->receiver->addBlock(new UserBlock($this->receiver, $this->sender, 'c'));

        $this->expectException(\DomainException::class);

        new MessageThread($this->sender, 'b', null, $this->receiver, 'a');
    }

    public function testThrowsExceptionWhenAddingReplyFromBlockedUser() {
        $this->sender = new User('u', 'p');
        $this->receiver = new User('u', 'p');

        $thread = new MessageThread($this->sender, 'b', null, $this->receiver, 'a');

        $this->receiver->addBlock(new UserBlock($this->receiver, $this->sender, 'c'));

        $this->expectException(\DomainException::class);

        $thread->addReply(new MessageReply($this->sender, 'b', null, $thread));
    }

    public function testBlockedUsersCannotReply() {
        $this->sender = new User('u', 'p');
        $this->receiver = new User('u', 'p');

        $thread = new MessageThread($this->sender, 'b', null, $this->receiver, 'a');

        $this->receiver->addBlock(new UserBlock($this->receiver, $this->sender, 'c'));

        $this->assertFalse($thread->userCanReply($this->sender));
    }
}
