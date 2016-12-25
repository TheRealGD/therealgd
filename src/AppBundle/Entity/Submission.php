<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="submissions")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="submission_type", type="string")
 * @ORM\DiscriminatorMap({"url": "Url", "post": "Post"})
 */
abstract class Submission {
    use VotableTrait;

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
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="submission", fetch="EXTRA_LAZY")
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
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="SubmissionVote", mappedBy="submission", fetch="EAGER", cascade={"persist"})
     *
     * @var SubmissionVote[]|Collection
     */
    private $votes;

    /**
     * Creates a new submission with an implicit upvote from the comment author.
     *
     * @param Forum $forum
     * @param User  $user
     *
     * @return static
     */
    public static function create(Forum $forum, User $user) {
        if (static::class === self::class) {
            throw new \BadMethodCallException(
                'This method must be called on an implementing class'
            );
        }

        $submission = new static();
        $submission->setForum($forum);
        $submission->setUser($user);

        $vote = new SubmissionVote();
        $vote->setUser($user);
        $vote->setSubmission($submission);
        $vote->setUpvote(true);

        /** @noinspection PhpUndefinedMethodInspection */
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
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
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
     * @return Collection|Comment[]
     */
    public function getComments() {
        return $this->comments;
    }

    /**
     * @param Collection|Comment[] $comments
     */
    public function setComments($comments) {
        $this->comments = $comments;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getTopLevelComments() {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('parent'));

        return $this->comments->matching($criteria);
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
     * @return SubmissionVote[]|Collection|Selectable
     */
    public function getVotes() {
        return $this->votes;
    }

    /**
     * @param SubmissionVote[]|Collection $votes
     */
    public function setVotes($votes) {
        $this->votes = $votes;
    }

    /**
     * Must return 'url' or 'post'.
     *
     * @return string
     */
    abstract public function getSubmissionType();
}
