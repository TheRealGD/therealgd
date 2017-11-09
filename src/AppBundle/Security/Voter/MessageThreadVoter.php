<?php

namespace AppBundle\Security\Voter;

use AppBundle\Entity\MessageThread;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageThreadVoter extends Voter {
    const ATTRIBUTES = ['access', 'reply'];

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject) {
        return $subject instanceof MessageThread && in_array($attribute, self::ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        if (!$subject instanceof MessageThread) {
            throw new \InvalidArgumentException('$subject must be '.MessageThread::class);
        }

        switch ($attribute) {
        case 'access':
            return $subject->userCanAccess($token->getUser());
        case 'reply':
            return $subject->userCanReply($token->getUser());
        default:
            throw new \LogicException('Unknown attribute '.$attribute);
        }
    }
}
