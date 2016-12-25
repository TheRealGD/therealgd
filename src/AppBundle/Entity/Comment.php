<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="comments")
 */
class Comment implements BodyInterface {
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
     * @Assert\Length(max=10000)
     *
     * @var string
     */
    private $rawBody;

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
     * @ORM\ManyToOne(targetEntity="User")
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
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="parent")
     *
     * @var Comment[]|Collection
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity="CommentVote", mappedBy="comment", fetch="EAGER", cascade={"persist"})
     *
     * @var CommentVote[]|Collection
     */
    private $votes;

    /**
     * Creates a new comment with an implicit upvote from the comment author.
     *
     * @param Submission   $submission
     * @param User         $user
     * @param Comment|null $parent
     *
     * @return Comment
     */
    public static function create(Submission $submission, User $user, Comment $parent = null) {
        $comment = new self();
        $comment->user = $user;
        $comment->submission = $submission;
        $comment->parent = $parent;

        $vote = new CommentVote();
        $vote->setUser($user);
        $vote->setComment($comment);
        $vote->setUpvote(true);

        $comment->votes->add($vote);

        return $comment;
    }

    public function __construct() {
        $this->timestamp = new \DateTime('@'.time());
        $this->children = new ArrayCollection();
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
     * {@inheritdoc}
     */
    public function getRawBody() {
        return $this->rawBody;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawBody($rawBody) {
        $this->rawBody = $rawBody;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function setBody($body) {
        $this->body = $body;
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
    public function setTimestamp(\DateTime $timestamp) {
        $this->timestamp = $timestamp;
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
     * @return Submission
     */
    public function getSubmission() {
        return $this->submission;
    }

    /**
     * @param Submission $submission
     */
    public function setSubmission($submission) {
        $this->submission = $submission;
    }

    /**
     * @return Comment
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param Comment $parent
     */
    public function setParent(Comment $parent) {
        $this->parent = $parent;
    }

    /**
     * @return Comment[]|Collection
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * @param Comment[]|Collection $children
     */
    public function setChildren($children) {
        $this->children = $children;
    }

    /**
     * @return Collection|CommentVote[]|Selectable
     */
    public function getVotes() {
        return $this->votes;
    }

    /**
     * @param Collection|CommentVote[] $votes
     */
    public function setVotes($votes) {
        $this->votes = $votes;
    }
}
