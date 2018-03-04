<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ForumLogCommentDeletion extends ForumLogEntry {
    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $author;

    /**
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @ORM\ManyToOne(targetEntity="Submission")
     *
     * @var Submission|null
     */
    private $submission;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $title;

    public function __construct(Comment $comment, User $user) {
        $this->author = $comment->getUser();
        $this->submission = $comment->getSubmission();
        $this->title = $this->submission->getTitle();

        parent::__construct($comment->getSubmission()->getForum(), $user);
    }

    public function getAuthor(): User {
        return $this->author;
    }

    public function getSubmission(): ?Submission {
        return $this->submission;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getAction(): string {
        return 'comment_deletion';
    }
}
