<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\Notification;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Form\UserSettingsType;
use Raddit\AppBundle\Form\UserType;
use Raddit\AppBundle\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class UserController extends Controller {
    /**
     * Show the user's profile page.
     *
     * @param User           $user
     * @param UserRepository $repository
     *
     * @return Response
     */
    public function userPageAction(User $user, UserRepository $repository) {
        $contributions = $repository->findLatestContributions($user);

        return $this->render('@RadditApp/user.html.twig', [
            'contributions' => $contributions,
            'user' => $user,
        ]);
    }

    /**
     * @param User $user
     * @param int  $page
     *
     * @return Response
     */
    public function submissionsAction(User $user, int $page) {
        $submissions = new Pagerfanta(new DoctrineCollectionAdapter($user->getSubmissions()));
        $submissions->setMaxPerPage(25);
        $submissions->setCurrentPage($page);

        return $this->render('@RadditApp/user_submissions.html.twig', [
            'submissions' => $submissions,
            'user' => $user,
        ]);
    }

    /**
     * @param User $user
     * @param int  $page
     *
     * @return Response
     */
    public function commentsAction(User $user, int $page) {
        $comments = new Pagerfanta(new DoctrineCollectionAdapter($user->getComments()));
        $comments->setMaxPerPage(25);
        $comments->setCurrentPage($page);

        return $this->render('@RadditApp/user_comments.html.twig', [
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
        $form = $this->createForm(UserType::class, $subject);
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

        return $this->render('@RadditApp/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $subject,
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
    public function userSettingsAction(User $subject, Request $request) {
        $form = $this->createForm(UserSettingsType::class, $subject);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', 'user_settings.update_notice');

                return $this->redirect($request->getUri());
            }
        } finally {
            $this->getDoctrine()->getManager()->refresh($subject);
        }

        return $this->render('@RadditApp/user_settings.html.twig', [
            'form' => $form->createView(),
            'user' => $subject,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @param int $page
     *
     * @return Response
     */
    public function inboxAction(int $page) {
        $notifications = $this->getDoctrine()->getRepository(Notification::class)
            ->findNotificationsInInbox($this->getUser(), $page);

        return $this->render('@RadditApp/inbox.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request       $request
     * @param ObjectManager $em
     * @param string        $_format
     *
     * @return Response
     */
    public function clearInboxAction(Request $request, ObjectManager $em, string $_format) {
        if (!$this->isCsrfTokenValid('clear_inbox', $request->request->get('token'))) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->getUser();
        $max = $request->query->getInt('max', null);

        $em->getRepository(Notification::class)->clearInbox($user, $max);
        $em->flush();

        if ($_format === 'json') {
            return $this->json(['message' => 'The inbox was successfully cleared.']);
        }

        $this->addFlash('notice', 'inbox.clear_notice');

        return $this->redirectToRoute('raddit_app_inbox');
    }
}
