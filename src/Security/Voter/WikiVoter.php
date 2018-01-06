<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\WikiPage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class WikiVoter extends Voter {
    const ATTRIBUTES = ['write'];

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
        return $subject instanceof WikiPage && in_array($attribute, self::ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        switch ($attribute) {
        case 'write':
            return $this->canWrite($subject, $token);
        default:
            throw new \LogicException("Unknown attribute '$attribute'");
        }
    }

    private function canWrite(WikiPage $page, TokenInterface $token): bool {
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // TODO: make this configurable
        if (!$user->isTrusted() && $user->getCreated() > new \DateTime('@'.time().' -24 hours')) {
            return false;
        }

        return !$page->isLocked();
    }
}
