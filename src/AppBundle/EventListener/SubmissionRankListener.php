<?php

namespace Raddit\AppBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\SubmissionVote;

/**
 * Updates the rank of a submission when voting on it.
 */
final class SubmissionRankListener implements EventSubscriber {
    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args) {
        $entity = $args->getEntity();

        if (!$entity instanceof SubmissionVote) {
            return;
        }

        $submission = $entity->getSubmission();
        $repository = $args->getEntityManager()->getRepository(Submission::class);
        $delta = $entity->isUpvote() ? 1 : -1;

        $repository->recalculateRank($submission, $delta);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args) {
        $entity = $args->getEntity();

        if (!$entity instanceof SubmissionVote) {
            return;
        }

        $submission = $entity->getSubmission();
        $repository = $args->getEntityManager()->getRepository(Submission::class);
        $delta = $entity->isUpvote() ? 2 : -2;

        $repository->recalculateRank($submission, $delta);
    }

    public function preRemove(LifecycleEventArgs $args) {
        $entity = $args->getEntity();

        if (!$entity instanceof SubmissionVote) {
            return;
        }

        $submission = $entity->getSubmission();
        $repository = $args->getEntityManager()->getRepository(Submission::class);
        $delta = $entity->isUpvote() ? -1 : 1;

        $repository->recalculateRank($submission, $delta);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents() {
        return ['prePersist', 'preUpdate', 'preRemove'];
    }
}
