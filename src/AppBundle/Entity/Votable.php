<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;

abstract class Votable {
    /**
     * @return Vote[]|Collection|Selectable
     */
    abstract public function getVotes();

    /**
     * @param Vote[]|Collection $votes
     */
    abstract public function setVotes($votes);

    /**
     * @return Vote
     */
    abstract public function createVote();

    /**
     * @return int
     */
    public function getUpvotes() {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('upvote', true));

        return count($this->getVotes()->matching($criteria));
    }

    /**
     * @return int
     */
    public function getDownvotes() {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('upvote', false));

        return count($this->getVotes()->matching($criteria));
    }

    /**
     * @return int
     */
    public function getNetScore() {
        return $this->getUpvotes() - $this->getDownvotes();
    }

    /**
     * @param User $user
     *
     * @return Vote|null
     */
    public function getUserVote(User $user) {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('user', $user));

        return $this->getVotes()->matching($criteria)->first();
    }

    /**
     * @param Votable $a
     * @param Votable $b
     *
     * @return int
     */
    public final static function descendingNetScoreCmp(self $a, self $b) {
        $as = $a->getNetScore();
        $bs = $b->getNetScore();

        if ($bs > $as) {
            return 1;
        }

        if ($as > $bs) {
            return -1;
        }

        return 0;
    }
}
