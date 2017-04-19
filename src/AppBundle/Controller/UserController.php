<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Form\UserSettingsType;
use Raddit\AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UserController extends Controller {
    /**
     * @param User $user
     *
     * @return Response
     */
    public function userPageAction(User $user) {
        $em = $this->getDoctrine()->getManager();

        $submissions = $em->getRepository(Submission::class)->findBy(
            ['user' => $user],
            ['id' => 'DESC'],
            25
        );

        $comments = $em->getRepository(Comment::class)->findBy(
            ['user' => $user],
            ['id' => 'DESC'],
            25
        );

        return $this->render('@RadditApp/user.html.twig', [
            'submissions' => $submissions,
            'comments' => $comments,
            'user' => $user,
        ]);
    }

    /**
     * User registration form.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function registrationAction(Request $request) {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('raddit_app_front');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('raddit_app_login');
        }

        return $this->render('@RadditApp/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("is_granted('edit_user', subject)")
     *
     * @param User    $subject
     * @param Request $request
     *
     * @return Response
     */
    public function editUserAction(User $subject, Request $request) {
        $form = $this->createForm(UserSettingsType::class, $subject);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $em->flush();

                return $this->redirectToRoute('raddit_app_edit_user', [
                    'username' => $subject->getUsername(),
                ]);
            }
        } finally {
            // Always reload the user object from the database. This avoids the
            // user in TokenStorage staying altered in case the form fails.
            $em->refresh($subject);
        }

        return $this->render('@RadditApp/edit-user.html.twig', [
            'form' => $form->createView(),
            'user' => $subject,
        ]);
    }
}
