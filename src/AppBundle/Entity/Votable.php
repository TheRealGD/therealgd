<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;

abstract class Votable {
    // these values should probably never change
    const USER_NO_VOTE = 0;
    const USER_UPVOTED = 1;
    const USER_DOWNVOTED = -1;

    const VOTE_UP = 1;
    const VOTE_DOWN = -1;
    const VOTE_RETRACT = 0;
    const VOTE_CHOICES = [self::VOTE_UP, self::VOTE_DOWN, self::VOTE_RETRACT];

    /**
     * @param Votable $a
     * @param Votable $b
     *
     * @return int
     */
    final public static function descendingNetScoreCmp(self $a, self $b) {
        return $b->getNetScore() <=> $a->getNetScore();
    }

    /**
     * @return Vote[]|Collection|Selectable
     */
    abstract public function getVotes();

    /**
     * @param User        $user
     * @param string|null $ip
     * @param int         $choice
     *
     * @return Vote
     */
    abstract protected function createVote(User $user, $ip, int $choice): Vote;

    /**
     * @param User        $user
     * @param string|null $ip
     * @param int         $choice
     *
     * @throws \InvalidArgumentException if the vote is not a VOTE_* constant
     */
    public function vote(User $user, $ip, int $choice) {
        $vote = $this->getUserVote($user);

        if ($choice === self::VOTE_UP || $choice === self::VOTE_DOWN) {
            if ($vote) {
                $vote->setChoice($choice);
            } else {
                $this->getVotes()->add($this->createVote($user, $ip, $choice));
            }
        } elseif ($choice === self::VOTE_RETRACT) {
            $this->getVotes()->removeElement($vote);
        } else {
            throw new \InvalidArgumentException('Bad vote choice');
        }
    }

    /**
     * @return int
     */
    public function getUpvotes(): int {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('upvote', true));

        return count($this->getVotes()->matching($criteria));
    }

    public function getDownvotes(): int {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('upvote', false));

        return count($this->getVotes()->matching($criteria));
    }

    public function getNetScore(): int {
        return $this->getUpvotes() - $this->getDownvotes();
    }

    public function getUserChoice(User $user): int {
        $vote = $this->getUserVote($user);

        if (!$vote) {
            return self::USER_NO_VOTE;
        }

        return $vote->getChoice();
    }

    /**
     * @param User $user
     *
     * @return null|Vote
     */
    private function getUserVote(User $user) {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        return $this->getVotes()->matching($criteria)->first() ?: null;
    }
}
