<?php

namespace App\Security\Voter;

use App\Entity\Forum;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ForumVoter extends Voter {
    const ATTRIBUTES = ['moderator', 'delete'];

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
        if (!$subject instanceof Forum) {
            throw new \InvalidArgumentException('$subject must be '.Forum::class);
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
        case 'moderator':
            return $subject->userIsModerator($user);
        case 'delete':
            return $subject->userCanDelete($user);
        default:
            throw new \InvalidArgumentException('Bad attribute '.$attribute);
        }
    }
}
