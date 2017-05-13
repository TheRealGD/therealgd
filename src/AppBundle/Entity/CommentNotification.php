<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class CommentNotification extends Notification {
    /**
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="notifications")
     *
     * @var Comment|null
     */
    private $comment;

    /**
     * @return Comment|null
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * @param Comment|null $comment
     */
    public function setComment($comment) {
        $this->comment = $comment;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string {
        return 'comment';
    }
}
