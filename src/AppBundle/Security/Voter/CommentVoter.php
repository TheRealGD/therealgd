<?php

namespace Raddit\AppBundle\Security\Voter;

use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class CommentVoter extends Voter {
    /**
     * - delete - Allowed to delete a thread *or* to soft-delete a comment.
     * - delete_thread - Ability to delete a comment with its replies.
     * - softdelete - Ability to soft delete a comment.
     */
    const ATTRIBUTES = ['delete', 'delete_thread', 'softdelete'];

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
        if (!in_array($attribute, self::ATTRIBUTES)) {
            return false;
        }

        if (!$subject instanceof Comment) {
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

        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        switch ($attribute) {
        case 'delete':
            return
                $this->canDeleteThread($subject, $token) ||
                $this->canSoftDelete($subject, $token);
        case 'delete_thread':
            return $this->canDeleteThread($subject, $token);
        case 'softdelete':
            return $this->canSoftDelete($subject, $token);
        default:
            throw new \InvalidArgumentException('Unknown attribute '.$attribute);
        }
    }

    /**
     * @param Comment        $comment
     * @param TokenInterface $token
     *
     * @return bool
     */
    private function canDeleteThread(Comment $comment, TokenInterface $token) {
        $forum = $comment->getSubmission()->getForum();

        // moderators can delete threads with or without replies
        if ($token->getUser()->isModeratorOfForum($forum)) {
            return true;
        }

        // non-forum mods and non-admins cannot delete threads with replies
        if (count($comment->getChildren()) > 0) {
            return false;
        }

        // users can delete their own comments
        return $token->getUser() === $comment->getUser();
    }

    /**
     * @param Comment        $comment
     * @param TokenInterface $token
     *
     * @return bool
     */
    private function canSoftDelete(Comment $comment, TokenInterface $token) {
        // users can delete their own comments
        if ($token->getUser() === $comment->getUser()) {
            return true;
        }

        $forum = $comment->getSubmission()->getForum();

        // moderators can soft-delete
        return $token->getUser()->isModeratorOfForum($forum);
    }
}
