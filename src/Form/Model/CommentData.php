<?php

namespace App\Form\Model;

use App\Entity\Comment;
use App\Entity\Submission;
use App\Entity\User;
use App\Entity\UserFlags;
use App\Validator\Constraints\NotForumBanned;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @NotForumBanned(forumPath="submission.forum", errorPath="body")
 */
class CommentData {
    /**
     * @var Submission
     */
    private $submission;

    /**
     * @Assert\NotBlank(message="The comment must not be empty.")
     * @Assert\Regex("/[[:graph:]]/u", message="The comment must not be empty.")
     * @Assert\Length(max=10000)
     *
     * @var string|null
     */
    private $body;

    /**
     * @var int|null
     */
    private $userFlag = UserFlags::FLAG_NONE;

    public static function createFromComment(Comment $comment): self {
        $self = new self($comment->getSubmission());
        $self->submission = $comment->getSubmission();
        $self->body = $comment->getBody();
        $self->userFlag = $comment->getUserFlag();

        return $self;
    }

    public function __construct(Submission $submission) {
        $this->submission = $submission;
    }

    public function toComment(User $user, Comment $parent = null, $ip = null): Comment {
        return new Comment(
            $this->body,
            $user,
            $this->submission,
            $this->userFlag,
            $parent,
            $ip
        );
    }

    public function updateComment(Comment $comment, User $editingUser) {
        $comment->setUserFlag($this->userFlag);

        if ($this->body !== $comment->getBody()) {
            $comment->setBody($this->body);
            $comment->setEditedAt(new \DateTime('@'.time()));

            if (!$comment->isModerated()) {
                $comment->setModerated($comment->getUser() !== $editingUser);
            }
        }
    }

    public function getBody(): ?string {
        return $this->body;
    }

    public function setBody($body): void {
        $this->body = $body;
    }

    public function getUserFlag(): ?int {
        return $this->userFlag;
    }

    public function setUserFlag($userFlag): void {
        $this->userFlag = $userFlag;
    }

    public function getSubmission(): Submission {
        return $this->submission;
    }
}
