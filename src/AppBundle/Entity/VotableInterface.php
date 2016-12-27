<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;

interface VotableInterface {
    /**
     * @return Vote[]|Collection|Selectable
     */
    public function getVotes();

    /**
     * @param Vote[]|Collection $votes
     */
    public function setVotes($votes);

    /**
     * @return int
     */
    public function getUpvotes();

    /**
     * @return int
     */
    public function getDownvotes();

    /**
     * @return int
     */
    public function getNetScore();

    /**
     * @param User $user
     *
     * @return Vote|null
     */
    public function getUserVote(User $user);

    /**
     * @return Vote
     */
    public function createVote();
}
