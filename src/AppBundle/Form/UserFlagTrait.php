<?php

namespace AppBundle\Form;

use AppBundle\Entity\Forum;
use AppBundle\Entity\UserFlags;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

trait UserFlagTrait {
    protected function addUserFlagOption(FormBuilderInterface $builder, Forum $forum = null) {
        /** @noinspection PhpUndefinedMethodInspection */
        $user = $this->tokenStorage->getToken()->getUser();

        $isModerator = $forum && $forum->userIsModerator($user, false);
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

            /* @noinspection PhpUnusedParameterInspection */
            $builder->add('userFlag', ChoiceType::class, [
                'choices' => $choices,
                'choice_label' => function ($key, $name) {
                    return 'user_flag.'.$name.'_label';
                },
                'label' => 'user_flag.post_as_label',
            ]);
        }
    }
}
