<?php

namespace Raddit\AppBundle\Security\Voter;

use Raddit\AppBundle\Entity\Submission;
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

        return $submission->getUser() === $token->getUser();
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

        return $token->getUser()->isModeratorOfForum($submission->getForum());
    }
}
