<?php


namespace App\Entity;

use App\Entity\ReportEntry;
use App\Entity\Submission;
use App\Entity\Comment;
use App\Entity\Forum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReportRepository")
 * @ORM\Table(name="report")
 */
class Report {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id()
     *
     * @var int
     */
    private $id;

    /**
     * Many reports have one forum.
     * @ORM\ManyToOne(targetEntity="Forum")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="id")
     */
    private $forum;

    /**
     * One report may have one submission.
     * @ORM\OneToOne(targetEntity="Submission")
     * @ORM\JoinColumn(name="submission_id", referencedColumnName="id", nullable=true)
     */
    private $submission;

    /**
     * One report may have one comment.
     * @ORM\OneToOne(targetEntity="Comment")
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $comment;

    /**
     * One report has any report entries.
     * @ORM\OneToMany(targetEntity="ReportEntry", mappedBy="report")
     */
    private $reportEntries;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isResolved = false;

    /**
     *
     */
    public function __construct() {
        $this->submission = null;
        $this->comment = null;
        $this->reportEntries = new ArrayCollection();
    }

    public function addReportEntry(ReportEntry $entry) {
        $this->reportEntries->add($entry);
    }

    public function setForum(Forum $forum) {
        $this->forum = $forum;
    }

    public function getSubmission() {
        return $this->submission;
    }

    public function setSubmission(Submission $submission) {
        $this->submission = $submission;
    }

    public function getComment() {
        return $this->comment;
    }

    public function setComment(Comment $comment) {
        $this->comment = $comment;
    }

    public function getEntries() {
        return $this->reportEntries;
    }

    public function getIsResolved() {
        return $this->isResolved;
    }

    public function setIsResolved($isResolved) {
        $this->isResolved = $isResolved;
    }
}
