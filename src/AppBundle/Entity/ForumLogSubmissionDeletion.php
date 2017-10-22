<?php

namespace Raddit\AppBundle\Entity;

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

    public function __construct(
        Forum $forum,
        User $user,
        bool $wasAdmin,
        string $title,
        User $author,
        \DateTime $timestamp = null
    ) {
        $this->title = $title;
        $this->author = $author;

        parent::__construct($forum, $user, $wasAdmin, $timestamp);
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getAuthor(): User {
        return $this->author;
    }

    public function getAction(): string {
        return 'submission_deletion';
    }
}
