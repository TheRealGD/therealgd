<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Entity\UserBlock;
use Raddit\AppBundle\Form\Model\UserBlockData;
use Raddit\AppBundle\Form\Model\UserData;
use Raddit\AppBundle\Form\UserBiographyType;
use Raddit\AppBundle\Form\UserBlockType;
use Raddit\AppBundle\Form\UserSettingsType;
use Raddit\AppBundle\Form\UserType;
use Raddit\AppBundle\Repository\NotificationRepository;
use Raddit\AppBundle\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
    public function userPage(User $user, UserRepository $repository) {
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
    public function submissions(User $user, int $page) {
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
    public function comments(User $user, int $page) {
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
    public function registration(
        Request $request,
        EntityManager $em,
        FirewallMap $firewallMap,
        TokenStorageInterface $tokenStorage,
        RememberMeServicesInterface $rememberMeServices
    ) {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('front');
        }

        $data = new UserData();
        $form = $this->createForm(UserType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $data->toUser();

            $em->persist($user);
            $em->flush();

            $response = $this->redirectToRoute('front');

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
     * @IsGranted("edit_user", subject="user")
     *
     * @param EntityManager $em
     * @param User          $user
     * @param Request       $request
     *
     * @return Response
     */
    public function editUser(EntityManager $em, User $user, Request $request) {
        $data = UserData::fromUser($user);

        $form = $this->createForm(UserType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateUser($user);

            $em->flush();

            return $this->redirectToRoute('edit_user', [
                'username' => $user->getUsername(),
            ]);
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @IsGranted("edit_user", subject="user")
     *
     * @param EntityManager $em
     * @param User          $user
     * @param Request       $request
     *
     * @return Response
     */
    public function userSettings(EntityManager $em, User $user, Request $request) {
        $data = UserData::fromUser($user);

        $form = $this->createForm(UserSettingsType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateUser($user);

            $em->flush();

            $this->addFlash('success', 'flash.user_settings_updated');

            return $this->redirect($request->getUri());
        }

        return $this->render('user/settings.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @IsGranted("edit_user", subject="user")
     *
     * @param EntityManager $em
     * @param User          $user
     * @param Request       $request
     *
     * @return Response
     */
    public function editBiography(EntityManager $em, User $user, Request $request) {
        $data = UserData::fromUser($user);

        $form = $this->createForm(UserBiographyType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateUser($user);

            $em->flush();

            $this->addFlash('success', 'flash.user_biography_updated');

            return $this->redirect($request->getUri());
        }

        return $this->render('user/edit_biography.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     *
     * @param int $page
     *
     * @return Response
     */
    public function blockList(int $page) {
        /* @var User $user */
        $user = $this->getUser();

        return $this->render('user/block_list.html.twig', [
            'blocks' => $user->getPaginatedBlocks($page),
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     *
     * @param User          $blockee
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function block(User $blockee, Request $request, EntityManager $em) {
        /* @var User $blocker */
        $blocker = $this->getUser();

        if ($blocker->isBlocking($blockee)) {
            throw $this->createNotFoundException('The user is already blocked');
        }

        $data = new UserBlockData();

        $form = $this->createForm(UserBlockType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $block = $data->toBlock($blocker, $blockee);

            $em->persist($block);
            $em->flush();

            $this->addFlash('success', 'flash.user_blocked');

            return $this->redirectToRoute('user_block_list');
        }

        return $this->render('user/block.html.twig', [
            'blockee' => $blockee,
            'form' => $form->createView(),
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
    public function unblock(UserBlock $block, EntityManager $em, Request $request) {
        if (!$this->isCsrfTokenValid('unblock', $request->request->get('token'))) {
            throw new AccessDeniedHttpException();
        }

        $em->remove($block);
        $em->flush();

        $this->addFlash('success', 'flash.user_unblocked');

        return $this->redirectToRoute('user_block_list');
    }

    /**
     * @IsGranted("ROLE_USER")
     *
     * @param int $page
     *
     * @return Response
     */
    public function inbox(int $page) {
        /* @var User $user */
        $user = $this->getUser();

        return $this->render('user/inbox.html.twig', [
            'notifications' => $user->getPaginatedNotifications($page),
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     *
     * @param Request                $request
     * @param NotificationRepository $nr
     * @param EntityManager          $em
     * @param string                 $_format
     *
     * @return Response
     */
    public function clearInbox(Request $request, NotificationRepository $nr, EntityManager $em, string $_format) {
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

        return $this->redirectToRoute('inbox');
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request       $request
     * @param User          $user
     * @param EntityManager $em
     * @param bool          $trusted
     *
     * @return Response
     */
    public function markAsTrusted(Request $request, User $user, EntityManager $em, bool $trusted) {
        if (!$this->isCsrfTokenValid('mark_trusted', $request->request->get('token'))) {
            throw new AccessDeniedHttpException();
        }

        $user->setTrusted($trusted);
        $em->flush();

        $this->addFlash('success', $trusted ? 'flash.user_marked_trusted' : 'flash.user_marked_untrusted');

        return $this->redirectToRoute('user', [
            'username' => $user->getUsername(),
        ]);
    }
}
