<?php

namespace App\Form\Model;

use App\Entity\Forum;
use App\Entity\Submission;
use App\Entity\User;
use App\Entity\UserFlags;
use App\Validator\Constraints\NotForumBanned;
use App\Validator\Constraints\RateLimit;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @RateLimit(period="1 hour", max="3", groups={"untrusted_user_create"},
 *     entityClass="App\Entity\Submission")
 */
class SubmissionData {
    private $entityId;

    /**
     * @Assert\NotBlank(groups={"create", "edit"})
     * @Assert\Length(max=300, groups={"create", "edit"})
     *
     * @var string|null
     */
    private $title;

    /**
     * @Assert\Length(max=2000, charset="8bit", groups={"create", "edit"})
     * @Assert\Url(protocols={"http", "https"}, groups={"create", "edit"})
     *
     * @see https://stackoverflow.com/questions/417142/
     *
     * @var string|null
     */
    private $url;

    /**
     * @Assert\Length(max=25000, groups={"create", "edit"})
     *
     * @var string|null
     */
    private $body;

    private $userFlag = UserFlags::FLAG_NONE;

    /**
     * @NotForumBanned()
     * @Assert\NotBlank(groups={"create", "edit"})
     *
     * @var Forum|null
     */
    private $forum;

    private $sticky = false;

    public function __construct(Forum $forum = null) {
        $this->forum = $forum;
    }

    public static function createFromSubmission(Submission $submission): self {
        $self = new self();
        $self->entityId = $submission->getId();
        $self->title = $submission->getTitle();
        $self->url = $submission->getUrl();
        $self->body = $submission->getBody();
        $self->userFlag = $submission->getUserFlag();
        $self->forum = $submission->getForum();
        $self->sticky = $submission->isSticky();

        return $self;
    }

    public function toSubmission(User $user, $ip): Submission {
        return new Submission(
            $this->title,
            $this->url,
            $this->body,
            $this->forum,
            $user,
            $ip,
            $this->sticky,
            $this->userFlag
        );
    }

    public function updateSubmission(Submission $submission, User $editingUser) {
        if (
            $this->url !== $submission->getUrl() ||
            $this->title !== $submission->getTitle() ||
            $this->body !== $submission->getBody()
        ) {
            $submission->setTitle($this->title);
            $submission->setUrl($this->url);
            $submission->setBody($this->body);
            $submission->setEditedAt(new \DateTime('@'.time()));

            if (!$submission->isModerated()) {
                $submission->setModerated($submission->getUser() !== $editingUser);
            }
        }

        $submission->setUserFlag($this->userFlag);
        $submission->setSticky($this->sticky);
    }

    public function getEntityId() {
        return $this->entityId;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function getBody() {
        return $this->body;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    public function getUserFlag() {
        return $this->userFlag;
    }

    public function setUserFlag($userFlag) {
        $this->userFlag = $userFlag;
    }

    public function getForum() {
        return $this->forum;
    }

    public function setForum($forum) {
        $this->forum = $forum;
    }

    public function isSticky(): bool {
        return $this->sticky;
    }

    public function setSticky(bool $sticky) {
        $this->sticky = $sticky;
    }
}
