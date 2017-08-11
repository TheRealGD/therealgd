<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Raddit\AppBundle\Validator\Constraints\RateLimit;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Raddit\AppBundle\Repository\SubmissionRepository")
 * @ORM\Table(name="submissions")
 *
 * @RateLimit(period="1 hour", max="3", groups={"untrusted_user_create"})
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
     * @Assert\NotBlank()
     * @Assert\Length(max=300)
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=2000, charset="8bit")
     * @Assert\Url(protocols={"http", "https"})
     *
     * @see https://stackoverflow.com/questions/417142/
     *
     * @var string
     */
    private $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=25000)
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
     * @Assert\NotBlank()
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
     * @ORM\Column(type="boolean")
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
     * Creates a new submission with an implicit upvote from its creator.
     *
     * @param Forum $forum
     * @param User  $user
     *
     * @return static
     */
    public static function create(Forum $forum = null, User $user) {
        $submission = new self();

        if ($forum) {
            $submission->setForum($forum);
        }

        $submission->setUser($user);
        $submission->vote($user, null, self::VOTE_UP);

        return $submission;
    }

    public function __construct() {
        $this->comments = new ArrayCollection();
        $this->timestamp = new \DateTime('@'.time());
        $this->votes = new ArrayCollection();
        $this->ranking = $this->timestamp->getTimestamp();
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @param string $body
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
     * Note: This method returns an actual array and not a {@link Collection}.
     *
     * @return Comment[]
     */
    public function getTopLevelComments() {
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

    /**
     * @return \DateTime
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     */
    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }

    /**
     * @return Forum
     */
    public function getForum() {
        return $this->forum;
    }

    /**
     * @param Forum $forum
     */
    public function setForum($forum) {
        $this->forum = $forum;
    }

    /**
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user) {
        $this->user = $user;
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

    /**
     * @param string|null $ip
     */
    public function setIp($ip) {
        $this->ip = $ip;
    }

    /**
     * @return bool
     */
    public function getSticky() {
        return $this->sticky;
    }

    /**
     * @param bool $sticky
     */
    public function setSticky($sticky) {
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

    /**
     * @return bool
     */
    public function isModerated(): bool {
        return $this->moderated;
    }

    /**
     * @param bool $moderated
     */
    public function setModerated(bool $moderated) {
        $this->moderated = $moderated;
    }

    /**
     * @return int
     */
    public function getUserFlag(): int {
        return $this->userFlag;
    }

    /**
     * @param int $userFlag
     */
    public function setUserFlag(int $userFlag) {
        if (!in_array($userFlag, UserFlags::FLAGS, true)) {
            throw new \InvalidArgumentException('Bad flag');
        }

        $this->userFlag = $userFlag;
    }
}
