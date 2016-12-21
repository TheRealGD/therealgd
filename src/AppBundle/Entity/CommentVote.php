<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="comment_votes")
 */
class CommentVote extends Vote {
    /**
     * @ORM\JoinColumn(nullable=false)
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
