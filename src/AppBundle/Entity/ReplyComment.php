<?php

namespace Raddit\AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ReplyComment extends Comment {
    /**
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="children")
     *
     * @var Comment
     */
    private $parent;

    /**
     * @return Comment
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param Comment $parent
     */
    public function setParent(Comment $parent) {
        $this->parent = $parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommentType() {
        return 'reply';
    }
}
