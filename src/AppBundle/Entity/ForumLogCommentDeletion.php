<?php

namespace Raddit\AppBundle\Entity;

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

    public function __construct(
        Forum $forum,
        User $user,
        bool $wasAdmin,
        User $author,
        Submission $submission,
        \DateTime $timestamp = null
    ) {
        $this->author = $author;
        $this->submission = $submission;
        $this->title = $submission->getTitle();

        parent::__construct($forum, $user, $wasAdmin, $timestamp);
    }

    public function getAuthor(): User {
        return $this->author;
    }

    /**
     * @return null|Submission
     */
    public function getSubmission() {
        return $this->submission;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getAction(): string {
        return 'comment_deletion';
    }
}
