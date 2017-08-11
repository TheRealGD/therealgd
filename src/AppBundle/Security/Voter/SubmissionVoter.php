<?php

namespace Raddit\AppBundle\Security\Voter;

use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class SubmissionVoter extends Voter {
    const ATTRIBUTES = ['edit', 'sticky'];

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
        case 'edit':
            return $this->canEdit($subject, $token);
        case 'sticky':
            return $this->canSticky($subject, $token);
        default:
            throw new \RuntimeException('Invalid attribute');
        }
    }

    /**
     * @param Submission     $submission
     * @param TokenInterface $token
     *
     * @return bool
     */
    private function canEdit(Submission $submission, TokenInterface $token) {
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

    /**
     * @param Submission     $submission
     * @param TokenInterface $token
     *
     * @return bool
     */
    private function canSticky(Submission $submission, TokenInterface $token) {
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $submission->getForum()->userIsModerator($token->getUser());
    }
}
