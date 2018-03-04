<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ForumLogSubmissionLock extends ForumLogEntry {
    /**
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @ORM\ManyToOne(targetEntity="Submission")
     *
     * @var Submission|null
     */
    private $submission;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $author;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $locked;

    public function __construct(Submission $submission, User $user, bool $locked) {
        $this->submission = $submission;
        $this->author = $submission->getUser();
        $this->title = $submission->getTitle();
        $this->locked = $locked;

        parent::__construct($submission->getForum(), $user);
    }

    public function getSubmission(): ?Submission {
        return $this->submission;
    }

    public function getAuthor(): User {
        return $this->author;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getLocked(): bool {
        return $this->locked;
    }

    public function getAction(): string {
        return 'submission_lock';
    }
}
