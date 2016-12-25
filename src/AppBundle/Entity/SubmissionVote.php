<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="submission_votes", uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="submission_user_vote_idx",
 *         columns={"submission_id", "user_id"}
 *     )
 * })
 */
class SubmissionVote extends Vote {
    /**
     * @ORM\JoinColumn(name="submission_id", nullable=false)
     * @ORM\ManyToOne(targetEntity="Submission", inversedBy="votes")
     *
     * @var Submission
     */
    private $submission;

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
}
