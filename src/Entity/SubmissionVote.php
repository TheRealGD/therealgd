<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="submission_votes", uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="submission_user_vote_idx",
 *         columns={"submission_id", "user_id"}
 *     )
 * })
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="submissionVotes")
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
     * {@inheritdoc}
     */
    public function __construct(User $user, ?string $ip, int $choice, Submission $submission) {
        parent::__construct($user, $ip, $choice);

        $this->submission = $submission;
    }

    public function getSubmission(): Submission {
        return $this->submission;
    }
}
