<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Entity\UserBlock;
use Raddit\AppBundle\Form\Model\UserBlockData;
use Raddit\AppBundle\Form\Model\UserSettings;
use Raddit\AppBundle\Form\UserBlockType;
use Raddit\AppBundle\Form\UserSettingsType;
use Raddit\AppBundle\Form\UserType;
use Raddit\AppBundle\Repository\NotificationRepository;
use Raddit\AppBundle\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;

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

        return $this->render('user/user.html.twig', [
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
        return $this->render('user/submissions.html.twig', [
            'submissions' => $user->getPaginatedSubmissions($page),
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
        return $this->render('user/comments.html.twig', [
            'comments' => $user->getPaginatedComments($page),
            'user' => $user,
        ]);
    }

    /**
     * User registration form.
     *
     * @param Request                     $request
     * @param EntityManager               $em
     * @param FirewallMap                 $firewallMap
     * @param TokenStorageInterface       $tokenStorage
     * @param RememberMeServicesInterface $rememberMeServices
     *
     * @return Response
     */
    public function registrationAction(
        Request $request,
        EntityManager $em,
        FirewallMap $firewallMap,
        TokenStorageInterface $tokenStorage,
        RememberMeServicesInterface $rememberMeServices
    ) {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('raddit_app_front');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            $response = $this->redirectToRoute('raddit_app_front');

            // log in with the new user
            $firewallName = $firewallMap->getFirewallConfig($request)->getName();
            $token = new RememberMeToken($user, $firewallName, $this->getParameter('env(SECRET)'));
            $tokenStorage->setToken($token);
            $rememberMeServices->loginSuccess($request, $response, $token);

            $this->addFlash('success', 'flash.user_account_registered');

            return $response;
        }

        return $this->render('user/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("is_granted('edit_user', subject)")
     *
     * @param EntityManager $em
     * @param User          $subject
     * @param Request       $request
     *
     * @return Response
     */
    public function editUserAction(EntityManager $em, User $subject, Request $request) {
        $form = $this->createForm(UserType::class, $subject);
        $form->handleRequest($request);

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

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $subject,
        ]);
    }

    /**
     * @Security("is_granted('edit_user', subject)")
     *
     * @param EntityManager $em
     * @param User          $subject
     * @param Request       $request
     *
     * @return Response
     */
    public function userSettingsAction(EntityManager $em, User $subject, Request $request) {
        $userSettings = UserSettings::fromUser($subject);

        $form = $this->createForm(UserSettingsType::class, $userSettings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userSettings->updateUser($subject);
            $em->flush();

            $this->addFlash('success', 'flash.user_settings_updated');

            return $this->redirect($request->getUri());
        }

        return $this->render('user/settings.html.twig', [
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
    public function blockListAction(int $page) {
        /* @var User $user */
        $user = $this->getUser();

        return $this->render('user/block_list.html.twig', [
            'blocks' => $user->getPaginatedBlocks($page),
        ]);
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @param User          $subject
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function blockAction(User $subject, Request $request, EntityManager $em) {
        /* @var User $user */
        $user = $this->getUser();

        if ($user->isBlocking($subject)) {
            throw $this->createNotFoundException('The user is already blocked');
        }

        $data = new UserBlockData();

        $form = $this->createForm(UserBlockType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $block = $data->toBlock($user, $subject);

            $em->persist($block);
            $em->flush();

            $this->addFlash('success', 'flash.user_blocked');

            return $this->redirectToRoute('raddit_app_user_block_list');
        }

        return $this->render('user/block.html.twig', [
            'form' => $form->createView(),
            'subject' => $subject,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_USER') and user === block.getBlocker()")
     *
     * @param UserBlock     $block
     * @param EntityManager $em
     * @param Request       $request
     *
     * @return Response
     */
    public function unblockAction(UserBlock $block, EntityManager $em, Request $request) {
        if (!$this->isCsrfTokenValid('unblock', $request->request->get('token'))) {
            throw new AccessDeniedHttpException();
        }

        $em->remove($block);
        $em->flush();

        $this->addFlash('success', 'flash.user_unblocked');

        return $this->redirectToRoute('raddit_app_user_block_list');
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @param int $page
     *
     * @return Response
     */
    public function inboxAction(int $page) {
        /* @var User $user */
        $user = $this->getUser();

        return $this->render('user/inbox.html.twig', [
            'notifications' => $user->getPaginatedNotifications($page),
        ]);
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request                $request
     * @param NotificationRepository $nr
     * @param EntityManager          $em
     * @param string                 $_format
     *
     * @return Response
     */
    public function clearInboxAction(Request $request, NotificationRepository $nr, EntityManager $em, string $_format) {
        if (!$this->isCsrfTokenValid('clear_inbox', $request->request->get('token'))) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->getUser();
        $max = $request->query->getInt('max', null);

        $nr->clearInbox($user, $max);
        $em->flush();

        if ($_format === 'json') {
            return $this->json(['message' => 'The inbox was successfully cleared.']);
        }

        $this->addFlash('notice', 'flash.inbox_cleared');

        return $this->redirectToRoute('raddit_app_inbox');
    }

    /**
     * @param Request       $request
     * @param User          $user
     * @param EntityManager $em
     * @param bool          $trusted
     *
     * @return Response
     */
    public function markAsTrustedAction(Request $request, User $user, EntityManager $em, bool $trusted) {
        if (!$this->isCsrfTokenValid('mark_trusted', $request->request->get('token'))) {
            throw new AccessDeniedHttpException();
        }

        $user->setTrusted($trusted);
        $em->flush();

        $this->addFlash('success', $trusted ? 'flash.user_marked_trusted' : 'flash.user_marked_untrusted');

        return $this->redirectToRoute('raddit_app_user', [
            'username' => $user->getUsername(),
        ]);
    }
}
