<?php

namespace App\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use App\Entity\Comment;
use App\Entity\Submission;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Marks comments and submissions as edited when certain fields are changed.
 * Also marks these as moderated when the logged in user isn't the original
 * author.
 */
final class EntityEditListener implements EventSubscriber {
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args) {
        $entity = $args->getEntity();

        if (
            $entity instanceof Comment && $this->commentIsEdited($args) ||
            $entity instanceof Submission && $this->submissionIsEdited($args)
        ) {
            $entity->setEditedAt(new \DateTime('@'.time()));

            if ($this->isModerated($args->getEntity())) {
                $entity->setModerated(true);
            }
        }
    }

    private function commentIsEdited(PreUpdateEventArgs $args): bool {
        return $args->hasChangedField('body');
    }

    private function submissionIsEdited(PreUpdateEventArgs $args): bool {
        return $args->hasChangedField('body') ||
            $args->hasChangedField('title') ||
            $args->hasChangedField('url');
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    private function isModerated($entity): bool {
        $token = $this->tokenStorage->getToken();

        return $token &&
            $token->getUser() instanceof User &&
            $token->getUser() !== $entity->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents() {
        return ['preUpdate'];
    }
}
