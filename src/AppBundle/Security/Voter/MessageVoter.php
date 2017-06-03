<?php

namespace Raddit\AppBundle\Security\Voter;

use Raddit\AppBundle\Entity\Message;
use Raddit\AppBundle\Entity\MessageReply;
use Raddit\AppBundle\Entity\MessageThread;
use Raddit\AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageVoter extends Voter {
    const ATTRIBUTES = ['access'];

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject) {
        return $subject instanceof Message && in_array($attribute, self::ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        switch ($attribute) {
        case 'access':
            return $this->canAccess($subject, $token);
        default:
            throw new \LogicException();
        }
    }

    private function canAccess(Message $subject, TokenInterface $token): bool {
        if ($subject->getSender() === $token->getUser()) {
            return true;
        }

        if ($subject instanceof MessageThread) {
            return $subject->getReceiver() === $token->getUser();
        }

        if ($subject instanceof MessageReply) {
            return $subject->getThread()->getReceiver() === $token->getUser();
        }

        throw new \LogicException();
    }
}
