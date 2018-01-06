<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Exception\BannedFromForumException;
use App\Entity\Exception\SubmissionLockedException;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 * @ORM\Table(name="comments")
 */
class Comment extends Votable {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $body;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="comments")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Submission", inversedBy="comments")
     *
     * @var Submission
     */
    private $submission;

    /**
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="children")
     *
     * @var Comment|null
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="parent", cascade={"remove"})
     *
     * @var Comment[]|Collection
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity="CommentVote", mappedBy="comment",
     *     fetch="EXTRA_LAZY", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var CommentVote[]|Collection
     */
    private $votes;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @var bool
     */
    private $softDeleted = false;

    /**
     * @ORM\Column(type="inet", nullable=true)
     *
     * @var string|null
     */
    private $ip;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     *
     * @var \DateTime|null
     */
    private $editedAt;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @var bool
     */
    private $moderated = false;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     *
     * @var int
     */
    private $userFlag = 0;

    /**
     * @ORM\OneToMany(targetEntity="CommentNotification", mappedBy="comment", cascade={"remove"})
     */
    private $notifications;

    public function __construct(
        string $body,
        User $user,
        Submission $submission,
        int $userFlag = UserFlags::FLAG_NONE,
        self $parent = null,
        $ip = null,
        \DateTime $timestamp = null
    ) {
        if ($ip !== null && !filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException('Invalid IP address');
        }

        if ($submission->isLocked() && !$user->isAdmin()) {
            throw new SubmissionLockedException();
        }

        if ($submission->getForum()->userIsBanned($user)) {
            throw new BannedFromForumException();
        }

        if ($parent) {
            $this->parent = $parent;
            $parent->children->add($this);
        }

        $this->body = $body;
        $this->setUserFlag($userFlag);
        $this->user = $user;
        $this->submission = $submission;
        $this->ip = $user->isTrusted() ? null : $ip;
        $this->timestamp = $timestamp ?: new \DateTime('@'.time());
        $this->children = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->vote($user, $ip, Votable::VOTE_UP);
        $this->notify();
        $this->notifications = null; // remove unused field warning
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getBody(): string {
        return $this->body;
    }

    public function setBody(string $body) {
        $this->body = $body;
    }

    public function getTimestamp(): \DateTime {
        return $this->timestamp;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getSubmission(): Submission {
        return $this->submission;
    }

    public function getParent(): ?Comment {
        return $this->parent;
    }

    /**
     * Get replies, ordered by descending net score.
     *
     * @return Comment[]
     */
    public function getChildren(): array {
        $children = $this->children->toArray();

        if ($children) {
            usort($children, [$this, 'descendingNetScoreCmp']);
        }

        return $children;
    }

    /**
     * {@inheritdoc}
     */
    public function getVotes(): Collection {
        return $this->votes;
    }

    protected function createVote(User $user, ?string $ip, int $choice): Vote {
        return new CommentVote($user, $ip, $choice, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function vote(User $user, ?string $ip, int $choice) {
        if ($this->submission->getForum()->userIsBanned($user)) {
            throw new BannedFromForumException();
        }

        parent::vote($user, $ip, $choice);
    }

    public function isSoftDeleted(): bool {
        return $this->softDeleted;
    }

    public function setSoftDeleted(bool $softDeleted) {
        $this->softDeleted = $softDeleted;
    }

    /**
     * Delete a comment without deleting its replies.
     */
    public function softDelete() {
        $this->softDeleted = true;
        $this->body = '';
    }

    public function getIp(): ?string {
        return $this->ip;
    }

    public function getEditedAt(): ?\DateTime {
        return $this->editedAt;
    }

    public function setEditedAt(?\DateTime $editedAt) {
        $this->editedAt = $editedAt;
    }

    public function isModerated(): bool {
        return $this->moderated;
    }

    public function setModerated(bool $moderated) {
        $this->moderated = $moderated;
    }

    public function getUserFlag(): int {
        return $this->userFlag;
    }

    public function setUserFlag(int $userFlag) {
        if (!in_array($userFlag, UserFlags::FLAGS, true)) {
            throw new \InvalidArgumentException('Bad flag');
        }

        $this->userFlag = $userFlag;
    }

    private function notify() {
        $receiver = ($this->parent ?: $this->submission)->getUser();

        if ($this->user === $receiver || $receiver->isBlocking($this->user)) {
            // don't send notification to oneself or to a blocking user
            return;
        }

        $receiver->sendNotification(new CommentNotification($receiver, $this));
    }
}
