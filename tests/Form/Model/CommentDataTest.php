<?php

namespace App\Tests\Form\Model;

use App\Entity\Comment;
use App\Entity\Submission;
use App\Entity\User;
use App\Form\Model\CommentData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @group time-sensitive
 */
class CommentDataTest extends TestCase {
    /**
     * @var Comment|MockObject
     */
    private $comment;

    public static function setUpBeforeClass() {
        ClockMock::register(CommentData::class);
    }

    protected function setUp() {
        $this->comment = $this->getMockBuilder(Comment::class)
            ->setMethods(['getSubmission', 'getUser'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->comment
            ->method('getSubmission')
            ->willReturn($this->createMock(Submission::class));

        $this->comment
            ->method('getUser')
            ->willReturn($this->createMock(User::class));

        $this->comment->setBody('foo');
    }

    public function testUpdate() {
        $data = CommentData::createFromComment($this->comment);
        $data->setBody('bar');
        $data->updateComment($this->comment, $this->comment->getUser());

        $this->assertEquals(new \DateTime('@'.time()), $this->comment->getEditedAt());
        $this->assertFalse($this->comment->isModerated());

        sleep(5);

        $data->setBody('baz');
        $data->updateComment($this->comment, $this->createMock(User::class));

        $this->assertEquals(new \DateTime('@'.time()), $this->comment->getEditedAt());
        $this->assertTrue($this->comment->isModerated());
    }
}
