<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="comment_votes", uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="comment_user_vote_idx",
 *         columns={"comment_id", "user_id"}
 *     )
 * })
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="commentVotes")
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

    public function __construct(User $user, ?string $ip, int $choice, Comment $comment) {
        parent::__construct($user, $ip, $choice);

        $this->comment = $comment;
    }

    public function getComment(): Comment {
        return $this->comment;
    }
}
