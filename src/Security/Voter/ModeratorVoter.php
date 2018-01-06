<?php

namespace App\Security\Voter;

use App\Entity\Moderator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ModeratorVoter extends Voter {
    protected function supports($attribute, $subject) {
        return $attribute === 'remove' && $subject instanceof Moderator;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        if (!$subject instanceof Moderator) {
            throw new \InvalidArgumentException('$subject must be '.Moderator::class);
        }

        switch ($attribute) {
        case 'remove':
            return $subject->userCanRemove($token->getUser());
        default:
            throw new \InvalidArgumentException('Invalid attribute '.$attribute);
        }
    }
}
