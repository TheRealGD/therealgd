<?php

namespace App\Security\Voter;

use App\Entity\Submission;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class SubmissionVoter extends Voter {
    const ATTRIBUTES = [
        'edit',
        'delete_with_reason',
        'delete_immediately',
        'sticky',
    ];

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager) {
        $this->decisionManager = $decisionManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject) {
        return $subject instanceof Submission && in_array($attribute, self::ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        switch ($attribute) {
        case 'delete_immediately':
            return $this->canDeleteImmediately($subject, $token);
        case 'delete_with_reason':
            return $this->canDeleteWithReason($subject, $token);
        case 'edit':
            return $this->canEdit($subject, $token);
        case 'sticky':
            return $this->canSticky($subject, $token);
        default:
            throw new \RuntimeException('Invalid attribute');
        }
    }

    private function canDeleteImmediately(Submission $submission, TokenInterface $token): bool {
        return $submission->getUser() === $token->getUser();
    }

    private function canDeleteWithReason(Submission $submission, TokenInterface $token): bool {
        return $submission->getForum()->userIsModerator($token->getUser());
    }

    private function canEdit(Submission $submission, TokenInterface $token): bool {
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if ($submission->getForum()->userIsModerator($token->getUser())) {
            return true;
        }

        if ($submission->getUser() === $token->getUser()) {
            // users can only edit if their submissions weren't moderated
            return !$submission->isModerated();
        }

        return false;
    }

    private function canSticky(Submission $submission, TokenInterface $token): bool {
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $submission->getForum()->userIsModerator($token->getUser());
    }
}
