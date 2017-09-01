<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Raddit\AppBundle\Entity\Exception\BannedFromForumException;

/**
 * @ORM\Entity(repositoryClass="Raddit\AppBundle\Repository\SubmissionRepository")
 * @ORM\Table(name="submissions")
 */
class Submission extends Votable {
    const NETSCORE_MULTIPLIER = 1800;
    const COMMENT_MULTIPLIER = 5000;
    const MAX_ADVANTAGE = 86400;
    const MAX_PENALTY = 10000;

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
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $body;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="submission",
     *     fetch="EXTRA_LAZY", cascade={"remove"})
     *
     * @var Comment[]|Collection
     */
    private $comments;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Forum", inversedBy="submissions")
     *
     * @var Forum
     */
    private $forum;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="submissions")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="SubmissionVote", mappedBy="submission",
     *     fetch="EAGER", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var SubmissionVote[]|Collection
     */
    private $votes;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $image;

    /**
     * @ORM\Column(type="inet", nullable=true)
     *
     * @var string|null
     */
    private $ip;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $sticky = false;

    /**
     * @ORM\Column(type="bigint")
     *
     * @var int
     */
    private $ranking;

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
    private $userFlag;

    /**
     * @param string         $title
     * @param string|null    $url
     * @param string|null    $body
     * @param Forum          $forum
     * @param User           $user
     * @param string|null    $ip
     * @param bool           $sticky
     * @param int            $userFlag
     * @param \DateTime|null $timestamp
     */
    public function __construct(
        string $title,
        $url,
        $body,
        Forum $forum,
        User $user,
        $ip,
        bool $sticky = false,
        int $userFlag = UserFlags::FLAG_NONE,
        \DateTime $timestamp = null
    ) {
        if ($ip !== null && !filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException('Invalid IP address');
        }

        if ($forum->userIsBanned($user)) {
            throw new BannedFromForumException();
        }

        $this->title = $title;
        $this->url = $url;
        $this->body = $body;
        $this->forum = $forum;
        $this->user = $user;
        $this->ip = $ip;
        $this->sticky = $sticky;
        $this->setUserFlag($userFlag);
        $this->timestamp = $timestamp ?: new \DateTime('@'.time());
        $this->comments = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->vote($user, $ip, Votable::VOTE_UP);
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title) {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setUrl($url) {
        $this->url = $url;
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
     * @return Collection|Comment[]
     */
    public function getComments() {
        return $this->comments;
    }

    /**
     * Get top-level comments, ordered by descending net score.
     *
     * @return Comment[]
     */
    public function getTopLevelComments(): array {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('parent'));

        $comments = $this->comments->matching($criteria)->toArray();

        if ($comments) {
            usort($comments, [$this, 'descendingNetScoreCmp']);
        }

        return $comments;
    }

    public function addComment(Comment $comment) {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }

        $this->updateRanking();
    }

    public function getTimestamp(): \DateTime {
        return $this->timestamp;
    }

    public function getForum(): Forum {
        return $this->forum;
    }

    public function getUser(): User {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function getVotes() {
        return $this->votes;
    }

    /**
     * {@inheritdoc}
     */
    protected function createVote(User $user, $ip, int $choice): Vote {
        return new SubmissionVote($user, $ip, $choice, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function vote(User $user, $ip, int $choice) {
        if ($this->forum->userIsBanned($user)) {
            throw new BannedFromForumException();
        }

        parent::vote($user, $ip, $choice);

        $this->updateRanking();
    }

    /**
     * @return string|null
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * @param string|null $image
     */
    public function setImage($image) {
        $this->image = $image;
    }

    /**
     * @return string|null
     */
    public function getIp() {
        return $this->ip;
    }

    public function isSticky(): bool {
        return $this->sticky;
    }

    public function setSticky(bool $sticky) {
        $this->sticky = $sticky;
    }

    /**
     * @return int
     */
    public function getRanking(): int {
        return $this->ranking;
    }

    public function updateRanking() {
        $netScoreAdvantage = $this->getNetScore() * self::NETSCORE_MULTIPLIER;
        $commentAdvantage = count($this->comments) * self::COMMENT_MULTIPLIER;

        $advantage = max(min($netScoreAdvantage + $commentAdvantage, self::MAX_ADVANTAGE), -self::MAX_PENALTY);

        $this->ranking = $this->getTimestamp()->getTimestamp() + $advantage;
    }

    /**
     * @return \DateTime|null
     */
    public function getEditedAt() {
        return $this->editedAt;
    }

    /**
     * @param \DateTime|null $editedAt
     */
    public function setEditedAt($editedAt) {
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
}
