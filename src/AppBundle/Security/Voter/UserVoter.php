<?php

namespace Raddit\AppBundle\Security\Voter;

use Raddit\AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserVoter extends Voter {
    const ATTRIBUTES = ['edit_user'];

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager) {
        $this->decisionManager = $decisionManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject) {
        return in_array($attribute, self::ATTRIBUTES) && $subject instanceof User;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        switch ($attribute) {
        case 'edit_user':
            return $this->canEditUser($subject, $token);
        default:
            throw new \InvalidArgumentException('Unknown attribute '.$attribute);
        }
    }

    /**
     * @param User           $user
     * @param TokenInterface $token
     *
     * @return bool
     */
    private function canEditUser(User $user, TokenInterface $token) {
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if (!$token->getUser() instanceof User) {
            return false;
        }

        return $user === $token->getUser();
    }
}
