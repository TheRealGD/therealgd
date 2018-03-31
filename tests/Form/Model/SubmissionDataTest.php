<?php

namespace App\Tests\Form\Model;

use App\Entity\Submission;
use App\Entity\User;
use App\Form\Model\SubmissionData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @group time-sensitive
 */
class SubmissionDataTest extends TestCase {
    /**
     * @var Submission|MockObject
     */
    private $submission;

    public static function setUpBeforeClass() {
        ClockMock::register(SubmissionData::class);
    }

    protected function setUp() {
        $this->submission = $this->getMockBuilder(Submission::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser', 'getForum'])
            ->getMock();

        $this->submission
            ->method('getUser')
            ->willReturn($this->createMock(User::class));

        $this->submission->setUserFlag(0);
        $this->submission->setTitle('foo');
        $this->submission->setUrl('http://example.com/');
        $this->submission->setBody('bar');
    }

    public function testUpdate() {
        $data = SubmissionData::createFromSubmission($this->submission);
        $data->setBody('bleh');
        $data->updateSubmission($this->submission, $this->submission->getUser());

        $this->assertEquals('bleh', $this->submission->getBody());
        $this->assertFalse($this->submission->isModerated());
        $this->assertEquals(new \DateTime('@'.time()), $this->submission->getEditedAt());

        sleep(5);

        $data->setTitle('poop');
        $data->updateSubmission($this->submission, $this->createMock(User::class));

        $this->assertTrue($this->submission->isModerated());
        $this->assertEquals(new \DateTime('@'.time()), $this->submission->getEditedAt());

        sleep(5);

        $data->setUrl('https://example.net/a');
        $data->updateSubmission($this->submission, $this->submission->getUser());

        $this->assertTrue($this->submission->isModerated());
        $this->assertEquals(new \DateTime('@'.time()), $this->submission->getEditedAt());
    }
}
