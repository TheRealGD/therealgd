<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Form\ForumAppearanceType;
use Raddit\AppBundle\Form\ForumBanType;
use Raddit\AppBundle\Form\ForumType;
use Raddit\AppBundle\Form\Model\ForumBanData;
use Raddit\AppBundle\Form\Model\ForumData;
use Raddit\AppBundle\Form\ModeratorType;
use Raddit\AppBundle\Form\PasswordConfirmType;
use Raddit\AppBundle\Repository\ForumBanRepository;
use Raddit\AppBundle\Repository\ForumCategoryRepository;
use Raddit\AppBundle\Repository\ForumRepository;
use Raddit\AppBundle\Repository\SubmissionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ForumController extends Controller {
    /**
     * Show the front page of a given forum.
     *
     * @ParamConverter("forum", options={
     *     "mapping": {"forum_name": "name"},
     *     "map_method_signature": true,
     *     "repository_method": "findOneByCaseInsensitiveName"
     * })
     *
     * @param SubmissionRepository $sr
     * @param Forum                $forum
     * @param string               $sortBy
     * @param int                  $page
     *
     * @return Response
     */
    public function frontAction(SubmissionRepository $sr, Forum $forum, string $sortBy, int $page) {
        $submissions = $sr->findForumSubmissions($forum, $sortBy, $page);

        return $this->render('forum/forum.html.twig', [
            'forum' => $forum,
            'sort_by' => $sortBy,
            'submissions' => $submissions,
        ]);
    }

    public function multiAction(ForumRepository $fr, SubmissionRepository $sr,
                                string $names, string $sortBy, int $page) {
        $names = preg_split('/[^\w]+/', $names, -1, PREG_SPLIT_NO_EMPTY);
        $names = array_map(Forum::class.'::canonicalizeName', $names);
        $names = $fr->findForumNames($names);

        if (!$names) {
            throw $this->createNotFoundException('no such forums');
        }

        $submissions = $sr->findFrontPageSubmissions($names, $sortBy, $page);

        return $this->render('forum/multi.html.twig', [
            'forums' => $names,
            'sort_by' => $sortBy,
            'submissions' => $submissions,
        ]);
    }

    /**
     * Create a new forum.
     *
     * @Security("is_granted('create_forum')")
     *
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function createForumAction(Request $request, EntityManager $em) {
        $data = new ForumData();

        $form = $this->createForm(ForumType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $forum = $data->toForum($this->getUser());

            $em->persist($forum);
            $em->flush();

            return $this->redirectToRoute('forum', [
                'forum_name' => $forum->getName(),
            ]);
        }

        return $this->render('forum/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("is_granted('moderator', forum)")
     * @ParamConverter("forum", options={
     *     "mapping": {"forum_name": "name"},
     *     "map_method_signature": true,
     *     "repository_method": "findOneByCaseInsensitiveName"
     * })
     *
     * @param Request       $request
     * @param Forum         $forum
     * @param EntityManager $em
     *
     * @return Response
     */
    public function editForumAction(Request $request, Forum $forum, EntityManager $em) {
        $data = ForumData::createFromForum($forum);

        $form = $this->createForm(ForumType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateForum($forum);

            $em->flush();

            $this->addFlash('success', 'flash.forum_updated');

            return $this->redirect($request->getUri());
        }

        return $this->render('forum/edit.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @ParamConverter("forum", options={
     *     "mapping": {"forum_name": "name"},
     *     "map_method_signature": true,
     *     "repository_method": "findOneByCaseInsensitiveName"
     * })
     *
     * @param Request       $request
     * @param Forum         $forum
     * @param EntityManager $em
     *
     * @return Response
     */
    public function deleteAction(Request $request, Forum $forum, EntityManager $em) {
        $form = $this->createForm(PasswordConfirmType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->remove($forum);
            $em->flush();

            $this->addFlash('success', 'flash.forum_deleted');

            return $this->redirectToRoute('front');
        }

        return $this->render('forum/delete.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request       $request
     * @param EntityManager $em
     * @param Forum         $forum   one of 'subscribe' or 'unsubscribe'
     * @param string        $action
     *
     * @return Response
     */
    public function subscribeAction(Request $request, EntityManager $em, Forum $forum, string $action) {
        if (!$this->isCsrfTokenValid('subscribe', $request->request->get('token'))) {
            throw $this->createAccessDeniedException();
        }

        if ($action === 'subscribe') {
            $forum->subscribe($this->getUser());
        } elseif ($action === 'unsubscribe') {
            $forum->unsubscribe($this->getUser());
        } else {
            throw new \InvalidArgumentException('$action must be subscribe or unsubscribe');
        }

        $em->flush();

        $referrer = $request->headers->get('Referer');

        if ($referrer) {
            return $this->redirect($referrer);
        }

        return $this->redirectToRoute('forum', ['forum_name' => $forum->getName()]);
    }

    /**
     * @param ForumRepository $repository
     * @param int             $page
     * @param string          $sortBy
     *
     * @return Response
     */
    public function listAction(ForumRepository $repository, int $page = 1, string $sortBy) {
        return $this->render('forum/list.html.twig', [
            'forums' => $repository->findForumsByPage($page, $sortBy),
            'sortBy' => $sortBy,
        ]);
    }

    /**
     * @param ForumCategoryRepository $fcr
     * @param ForumRepository         $fr
     *
     * @return Response
     */
    public function listCategoriesAction(ForumCategoryRepository $fcr, ForumRepository $fr) {
        $forumCategories = $fcr->findBy([], ['name' => 'ASC']);
        $uncategorizedForums = $fr->findBy(['category' => null], ['canonicalName' => 'ASC']);

        return $this->render('forum/list_by_category.html.twig', [
            'forum_categories' => $forumCategories,
            'uncategorized_forums' => $uncategorizedForums,
        ]);
    }

    /**
     * Show a list of forum moderators.
     *
     * @ParamConverter("forum", options={
     *     "mapping": {"forum_name": "name"},
     *     "map_method_signature": true,
     *     "repository_method": "findOneByCaseInsensitiveName"
     * })
     *
     * @param Forum $forum
     * @param int   $page
     *
     * @return Response
     */
    public function moderatorsAction(Forum $forum, int $page) {
        return $this->render('forum/moderators.html.twig', [
            'forum' => $forum,
            'moderators' => $forum->getPaginatedModerators($page),
        ]);
    }

    /**
     * @ParamConverter("forum", options={
     *     "mapping": {"forum_name": "name"},
     *     "map_method_signature": true,
     *     "repository_method": "findOneByCaseInsensitiveName"
     * })
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param EntityManager $em
     * @param Forum         $forum
     * @param Request       $request
     *
     * @return Response
     */
    public function addModeratorAction(EntityManager $em, Forum $forum, Request $request) {
        $form = $this->createForm(ModeratorType::class, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $forum->addUserAsModerator($form->getData()['user']);

            $em->flush();

            $this->addFlash('success', 'flash.forum_moderator_added');

            return $this->redirectToRoute('forum_moderators', [
                'forum_name' => $forum->getName(),
            ]);
        }

        return $this->render('forum/add_moderator.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }

    /**
     * Alter a forum's appearance.
     *
     * @Security("is_granted('moderator', forum)")
     *
     * @ParamConverter("forum", options={
     *     "mapping": {"forum_name": "name"},
     *     "map_method_signature": true,
     *     "repository_method": "findOneByCaseInsensitiveName"
     * })
     *
     * @param Forum         $forum
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function appearanceAction(Forum $forum, Request $request, EntityManager $em) {
        $data = ForumData::createFromForum($forum);

        $form = $this->createForm(ForumAppearanceType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateForum($forum);

            $em->flush();

            return $this->redirectToRoute('forum_appearance', [
                'forum_name' => $forum->getName(),
            ]);
        }

        return $this->render('forum/appearance.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }

    /**
     * @Security("is_granted('moderator', forum)")
     *
     * @ParamConverter("forum", options={
     *     "mapping": {"forum_name": "name"},
     *     "map_method_signature": true,
     *     "repository_method": "findOneByCaseInsensitiveName"
     * })
     *
     * @param Forum              $forum
     * @param ForumBanRepository $banRepository
     * @param int                $page
     *
     * @return Response
     */
    public function bansAction(Forum $forum, ForumBanRepository $banRepository, int $page = 1) {
        return $this->render('forum/bans.html.twig', [
            'bans' => $banRepository->findValidBansInForum($forum, $page),
            'forum' => $forum,
        ]);
    }

    /**
     * @Security("is_granted('moderator', forum)")
     *
     * @ParamConverter("forum", options={
     *     "mapping": {"forum_name": "name"},
     *     "map_method_signature": true,
     *     "repository_method": "findOneByCaseInsensitiveName"
     * })
     *
     * @param Forum $forum
     * @param User  $subject
     * @param int   $page
     *
     * @return Response
     */
    public function banHistoryAction(Forum $forum, User $subject, int $page = 1) {
        return $this->render('forum/ban_history.html.twig', [
            'bans' => $forum->getPaginatedBansByUser($subject, $page),
            'forum' => $forum,
            'user' => $subject,
        ]);
    }

    /**
     * @Security("is_granted('moderator', forum)")
     *
     * @ParamConverter("forum", options={
     *     "mapping": {"forum_name": "name"},
     *     "map_method_signature": true,
     *     "repository_method": "findOneByCaseInsensitiveName"
     * })
     *
     * @param Forum         $forum
     * @param User          $subject
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function banAction(Forum $forum, User $subject, Request $request, EntityManager $em) {
        $data = new ForumBanData();

        $form = $this->createForm(ForumBanType::class, $data, ['intent' => 'ban']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $forum->addBan($data->toBan($forum, $subject, $this->getUser()));

            $em->flush();

            $this->addFlash('success', 'flash.user_was_banned');

            return $this->redirectToRoute('forum_bans', [
                'forum_name' => $forum->getName(),
            ]);
        }

        return $this->render('forum/ban.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
            'user' => $subject,
        ]);
    }

    /**
     * @Security("is_granted('moderator', forum)")
     *
     * @ParamConverter("forum", options={
     *     "mapping": {"forum_name": "name"},
     *     "map_method_signature": true,
     *     "repository_method": "findOneByCaseInsensitiveName"
     * })
     *
     * @param Forum         $forum
     * @param User          $subject
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function unbanAction(Forum $forum, User $subject, Request $request, EntityManager $em) {
        $data = new ForumBanData();

        $form = $this->createForm(ForumBanType::class, $data, ['intent' => 'unban']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $forum->addBan($data->toUnban($forum, $subject, $this->getUser()));

            $em->flush();

            $this->addFlash('success', 'flash.user_was_unbanned');

            return $this->redirectToRoute('forum_bans', [
                'forum_name' => $forum->getName(),
            ]);
        }

        return $this->render('forum/unban.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
            'user' => $subject,
        ]);
    }
}
