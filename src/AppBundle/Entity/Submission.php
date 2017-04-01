<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Raddit\AppBundle\Repository\SubmissionRepository")
 * @ORM\Table(name="submissions")
 */
class Submission extends Votable implements BodyInterface {
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
     * @var string
     */
    private $body;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=25000)
     *
     * @var string
     */
    private $rawBody;

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
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="SubmissionVote", mappedBy="submission",
     *     fetch="EAGER", cascade={"persist", "remove"})
     *
     * @var SubmissionVote[]|Collection
     */
    private $votes;

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

        $vote = new SubmissionVote();
        $vote->setUser($user);
        $vote->setSubmission($submission);
        $vote->setUpvote(true);

        $submission->getVotes()->add($vote);

        return $submission;
    }

    public function __construct() {
        $this->comments = new ArrayCollection();
        $this->timestamp = new \DateTime('@'.time());
        $this->votes = new ArrayCollection();
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
     * @return string
     */
    public function getRawBody() {
        return $this->rawBody;
    }

    /**
     * @param string $rawBody
     */
    public function setRawBody($rawBody) {
        $this->rawBody = $rawBody;
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
    public function createVote() {
        $vote = new SubmissionVote();
        $vote->setSubmission($this);

        return $vote;
    }
}
