<?php

namespace Raddit\AppBundle\Security\Voter;

use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ForumVoter extends Voter {
    const ATTRIBUTES = ['edit', 'delete'];

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
        return $subject instanceof Forum && in_array($attribute, self::ATTRIBUTES);
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
        case 'delete':
            return $this->canDelete($token);
        default:
            throw new \InvalidArgumentException('Bad attribute '.$attribute);
        }
    }

    private function canEdit(Forum $forum, TokenInterface $token) {
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        /** @var User $user */
        $user = $token->getUser();

        return $forum->userIsModerator($user);
    }

    /**
     * @param TokenInterface $token
     *
     * @return bool
     */
    private function canDelete(TokenInterface $token) {
        return $this->decisionManager->decide($token, ['ROLE_ADMIN']);
    }
}
