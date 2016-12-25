<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;

trait VotableTrait {
    /**
     * @return Vote[]|Collection|Selectable
     */
    abstract public function getVotes();

    /**
     * @param Vote[]|Collection $votes
     */
    abstract public function setVotes($votes);

    /**
     * Get the net score for this entity.
     *
     * @return int
     */
    public function getNetScore() {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('upvote', true));
        $upvotes = count($this->getVotes()->matching($criteria));

        $criteria->where(Criteria::expr()->eq('upvote', false));
        $downvotes = count($this->getVotes()->matching($criteria));

        return $upvotes - $downvotes;
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
}
