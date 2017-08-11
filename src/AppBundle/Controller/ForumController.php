<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Moderator;
use Raddit\AppBundle\Form\ForumAppearanceType;
use Raddit\AppBundle\Form\ForumType;
use Raddit\AppBundle\Form\ModeratorType;
use Raddit\AppBundle\Form\PasswordConfirmType;
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

        return $this->render('@RadditApp/forum.html.twig', [
            'forum' => $forum,
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
        $forum = new Forum();

        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $moderator = new Moderator();
            $moderator->setUser($this->getUser());
            $moderator->setForum($forum);
            $forum->addModerator($moderator);
            $forum->subscribe($this->getUser());

            $em->persist($forum);
            $em->flush();

            return $this->redirectToRoute('raddit_app_forum', [
                'forum_name' => $forum->getName(),
            ]);
        }

        return $this->render('@RadditApp/create_forum.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("is_granted('edit', forum)")
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
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'flash.forum_updated');

            return $this->redirect($request->getUri());
        }

        return $this->render('@RadditApp/edit_forum.html.twig', [
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

            return $this->redirectToRoute('raddit_app_front');
        }

        return $this->render('@RadditApp/forum_delete.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request       $request
     * @param EntityManager $em
     * @param Forum         $forum one of 'subscribe' or 'unsubscribe'
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

        return $this->redirectToRoute('raddit_app_forum', ['forum_name' => $forum->getName()]);
    }

    /**
     * @param ForumRepository $repository
     * @param int             $page
     * @param string          $sortBy
     *
     * @return Response
     */
    public function listAction(ForumRepository $repository, int $page = 1, string $sortBy) {
        return $this->render('@RadditApp/forum_list.html.twig', [
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

        return $this->render('@RadditApp/forums_by_category.html.twig', [
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
        return $this->render('@RadditApp/forum_moderators.html.twig', [
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
        $moderator = new Moderator();
        $moderator->setForum($forum);

        $form = $this->createForm(ModeratorType::class, $moderator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($moderator);
            $em->flush();

            $this->addFlash('success', 'flash.forum_moderator_added');

            return $this->redirectToRoute('raddit_app_forum_moderators', [
                'forum_name' => $forum->getName(),
            ]);
        }

        return $this->render('@RadditApp/add_moderator.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }

    /**
     * Alter a forum's appearance.
     *
     * @Security("is_granted('edit', forum)")
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
        $form = $this->createForm(ForumAppearanceType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('raddit_app_forum_appearance', [
                'forum_name' => $forum->getName(),
            ]);
        }

        return $this->render('@RadditApp/forum_appearance.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }
}
