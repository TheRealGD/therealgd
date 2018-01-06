<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserVoter extends Voter {
    const ATTRIBUTES = ['edit_user', 'message'];

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
        if (!$subject instanceof User) {
            throw new \InvalidArgumentException('$subject must be '.User::class);
        }

        switch ($attribute) {
        case 'edit_user':
            // TODO: move to user entity
            return $this->canEditUser($subject, $token);
        case 'message':
            return $subject->canBeMessagedBy($token->getUser());
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
