<?php

namespace App\Security\Voter;

use App\Entity\User;
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
     * @var string|null
     */
    private $forumCreationInterval;

    /**
     * @param AccessDecisionManagerInterface $decisionManager
     * @param string|null                    $forumCreationInterval
     */
    public function __construct(
        AccessDecisionManagerInterface $decisionManager,
        string $forumCreationInterval = null
    ) {
        $this->decisionManager = $decisionManager;
        $this->forumCreationInterval = $forumCreationInterval;
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
        if (!$token->getUser() instanceof User) {
            return false;
        }

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
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if ($this->forumCreationInterval) {
            $now = new \DateTime('@'.time());
            $maxDate = (clone $now)->modify($this->forumCreationInterval);
            $maxDate = $now->sub($now->diff($maxDate, true));

            /* @var User $user */
            $user = $token->getUser();

            if ($user->getCreated() > $maxDate) {
                return false;
            }
        }

        return true;
    }
}
