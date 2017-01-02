<?php

namespace Raddit\AppBundle\Security\Voter;

use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter {
    /**
     * @var array
     */
    const ATTRIBUTES = ['delete'];

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker) {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject) {
        if (!$subject instanceof Comment) {
            return false;
        }

        if (!in_array($attribute, self::ATTRIBUTES)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        switch ($attribute) {
        case 'delete':
            return $this->canDeleteComment($subject, $token);
            break;
        default:
            throw new \InvalidArgumentException('Unknown attribute');
        }
    }

    /**
     * @param Comment        $comment
     * @param TokenInterface $token
     *
     * @return bool
     */
    private function canDeleteComment(Comment $comment, TokenInterface $token) {
        if ($comment->getUser() === $token->getUser()) {
            return true;
        }

        if ($$token->getUser()-)
    }
}
