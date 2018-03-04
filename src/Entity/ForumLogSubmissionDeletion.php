<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ForumLogSubmissionDeletion extends ForumLogEntry {
    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $author;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $reason;

    public function __construct(Submission $submission, User $user, string $reason) {
        $this->title = $submission->getTitle();
        $this->author = $submission->getUser();
        $this->reason = $reason;

        parent::__construct($submission->getForum(), $user);
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getAuthor(): User {
        return $this->author;
    }

    public function getReason(): ?string {
        return $this->reason;
    }

    public function getAction(): string {
        return 'submission_deletion';
    }
}
