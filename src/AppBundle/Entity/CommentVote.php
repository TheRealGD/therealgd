<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="comment_votes", uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="comment_user_vote_idx",
 *         columns={"comment_id", "user_id"}
 *     )
 * })
 */
class CommentVote extends Vote {
    /**
     * @ORM\JoinColumn(name="comment_id", nullable=false)
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="votes")
     *
     * @var Comment
     */
    private $comment;

    /**
     * @return Comment
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * @param Comment $comment
     */
    public function setComment($comment) {
        $this->comment = $comment;
    }
}
