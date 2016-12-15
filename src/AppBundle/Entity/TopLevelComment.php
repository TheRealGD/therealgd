<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class TopLevelComment extends Comment {
    /**
     * @ORM\ManyToOne(targetEntity="Submission", inversedBy="comments")
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

    /**
     * {@inheritdoc}
     */
    public function getCommentType() {
        return 'top';
    }
}
