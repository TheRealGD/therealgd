<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Votable;
use App\Entity\Vote;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class VotableTest extends TestCase {
    /**
     * @var Votable
     */
    private $votable;

    protected function setUp() {
        $this->votable = $this->createVotable();
    }

    public function testVotableScores(): void {
        $votable = $this->createVotable();
        $user = $this->createMock(User::class);

        $this->assertEquals(0, $votable->getNetScore());
        $this->assertEquals(0, $votable->getUpvotes());
        $this->assertEquals(0, $votable->getDownvotes());

        $votable->vote($user, null, Votable::VOTE_UP);

        $this->assertEquals(1, $votable->getNetScore());
        $this->assertEquals(1, $votable->getUpvotes());
        $this->assertEquals(0, $votable->getDownvotes());

        $votable->vote($user, null, Votable::VOTE_DOWN);

        $this->assertEquals(-1, $votable->getNetScore());
        $this->assertEquals(0, $votable->getUpvotes());
        $this->assertEquals(1, $votable->getDownvotes());
    }

    public function testVoteCollectionHasCorrectProperties(): void {
        $user = $this->createMock(User::class);

        $this->votable->vote($user, null, Votable::VOTE_UP);
        $this->assertEquals(Votable::USER_UPVOTED, $this->votable->getVotes()->first()->getChoice());
        $this->assertCount(1, $this->votable->getVotes());

        $this->votable->vote($user, null, Votable::VOTE_DOWN);
        $this->assertEquals(Votable::USER_DOWNVOTED, $this->votable->getVotes()->first()->getChoice());
        $this->assertCount(1, $this->votable->getVotes());

        $this->votable->vote($user, null, Votable::VOTE_RETRACT);
        $this->assertCount(0, $this->votable->getVotes());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCannotGiveIncorrectVote(): void {
        $user = $this->createMock(User::class);

        $this->votable->vote($user, null, 69);
    }

    public function testGetUserVote(): void {
        $user1 = $this->createMock(User::class);
        $this->votable->vote($user1, null, Votable::VOTE_UP);

        $user2 = $this->createMock(User::class);
        $this->votable->vote($user2, null, Votable::VOTE_DOWN);

        $user3 = $this->createMock(User::class);

        $this->assertEquals(Votable::USER_UPVOTED, $this->votable->getUserChoice($user1));
        $this->assertEquals(Votable::USER_DOWNVOTED, $this->votable->getUserChoice($user2));
        $this->assertEquals(Votable::USER_NO_VOTE, $this->votable->getUserChoice($user3));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAcceptsWellFormedIpAddresses(): void {
        $user = $this->createMock(User::class);
        $this->votable->vote($user, '127.0.4.20', Votable::VOTE_UP);
        $this->votable->vote($user, '::69', Votable::VOTE_UP);
        $this->votable->vote($user, null, Votable::VOTE_UP);
    }

    public function testThrowsExceptionOnBadIpAddress(): void {
        $user = $this->createMock(User::class);

        $this->expectException(\InvalidArgumentException::class);

        $this->votable->vote($user, 'poop', Votable::VOTE_UP);
    }

    /**
     * @return Votable
     */
    private function createVotable(): Votable {
        return new class() extends Votable {
            private $votes;

            public function __construct() {
                $this->votes = new ArrayCollection();
            }

            public function getVotes(): Collection {
                return $this->votes;
            }

            protected function createVote(User $user, ?string $ip, int $choice): Vote {
                return new class($user, $ip, $choice) extends Vote {};
            }
        };
    }
}
