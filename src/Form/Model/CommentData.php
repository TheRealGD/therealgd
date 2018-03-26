<?php

namespace App\Form\Model;

use App\Entity\Comment;
use App\Entity\Submission;
use App\Entity\User;
use App\Entity\UserFlags;
use App\Validator\Constraints\RateLimit;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @RateLimit(period="1 hour", max="3", groups={"untrusted_user_comment"},
 *     entityClass="App\Entity\Comment")
 */
class CommentData {
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
        $self = new self();
        $self->body = $comment->getBody();
        $self->userFlag = $comment->getUserFlag();

        return $self;
    }

    public function toComment(
        Submission $submission,
        User $user,
        Comment $parent = null,
        $ip = null
    ): Comment {
        return new Comment(
            $this->body,
            $user,
            $submission,
            $this->userFlag,
            $parent,
            $ip
        );
    }

    public function updateComment(Comment $comment) {
        $comment->setBody($this->body);
        $comment->setUserFlag($this->userFlag);
    }

    /**
     * @return string|null
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @param string|null $body
     */
    public function setBody($body) {
        $this->body = $body;
    }

    /**
     * @return int|null
     */
    public function getUserFlag() {
        return $this->userFlag;
    }

    /**
     * @param int|null $userFlag
     */
    public function setUserFlag($userFlag) {
        $this->userFlag = $userFlag;
    }
}
