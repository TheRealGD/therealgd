<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Form\RegistrationType;
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
        $form = $this->createForm(RegistrationType::class, $user);
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
}
