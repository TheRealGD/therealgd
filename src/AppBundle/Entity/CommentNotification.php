<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class CommentNotification extends Notification {
    /**
     * @ORM\ManyToOne(targetEntity="Comment")
     *
     * @var Comment
     */
    private $comment;

    public function __construct(User $receiver, Comment $comment) {
        parent::__construct($receiver);

        $this->comment = $comment;
    }

    public function getComment(): Comment {
        return $this->comment;
    }

    public function getType(): string {
        return 'comment';
    }
}
