<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Entity\UserFlags;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

trait UserFlagTrait {
    protected function addUserFlagOption(FormBuilderInterface $builder, array $options) {
        $isModerator = $this->isModerator($builder->getData(), $options);
        /** @noinspection PhpUndefinedMethodInspection */
        $isAdmin = $this->authorizationChecker->isGranted('ROLE_ADMIN');

        if ($isModerator || $isAdmin) {
            $choices = ['none' => UserFlags::FLAG_NONE];

            if ($isModerator) {
                $choices['moderator'] = UserFlags::FLAG_MODERATOR;
            }

            if ($isAdmin) {
                $choices['admin'] = UserFlags::FLAG_ADMIN;
            }

            /** @noinspection PhpUnusedParameterInspection */
            $builder->add('userFlag', ChoiceType::class, [
                'choices' => $choices,
                'choice_label' => function ($key, $name) {
                    return 'user_flag.'.$name.'_label';
                },
                'label' => 'user_flag.post_as_label',
            ]);
        }
    }

    private function isModerator($comment, array $options): bool {
        /** @noinspection PhpUndefinedMethodInspection */
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($options['forum']) {
            $forum = $options['forum'];
        } elseif (
            $comment instanceof Comment &&
            $comment->getSubmission() instanceof Submission &&
            $comment->getSubmission()->getForum() instanceof Forum
        ) {
            $forum = $comment->getSubmission()->getForum();
        } else {
            return false;
        }

        return $user->isModeratorOfForum($forum);
    }
}
