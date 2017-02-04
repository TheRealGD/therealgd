<?php

namespace Raddit\AppBundle\Security\Voter;

use Raddit\AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class TokenVoter extends Voter {
    const ATTRIBUTES = [
        self::CREATE_FORUM,
    ];

    const CREATE_FORUM = 'create_forum';

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
        return in_array($attribute, self::ATTRIBUTES) && $subject === null;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        switch ($attribute) {
        case self::CREATE_FORUM:
            return $this->canCreateForum($token);
        default:
            throw new \InvalidArgumentException('Unknown attribute '.$attribute);
        }
    }

    /**
     * @param TokenInterface $token
     *
     * @return bool
     */
    private function canCreateForum(TokenInterface $token) {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        // TODO - use a different role once users can have different roles
        return $this->decisionManager->decide($token, ['ROLE_USER']);
    }
}
