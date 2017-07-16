<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\ForumCategory;
use Raddit\AppBundle\Entity\ForumSubscription;
use Raddit\AppBundle\Entity\Moderator;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Form\ForumAppearanceType;
use Raddit\AppBundle\Form\ForumType;
use Raddit\AppBundle\Form\ModeratorType;
use Raddit\AppBundle\Repository\ForumRepository;
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
     * @param ObjectManager $om
     * @param Forum         $forum
     * @param string        $sortBy
     * @param int           $page
     *
     * @return Response
     */
    public function frontAction(ObjectManager $om, Forum $forum, string $sortBy, int $page) {
        $submissions = $om->getRepository(Submission::class)
            ->findForumSubmissions($forum, $sortBy, $page);

        return $this->render('@RadditApp/forum.html.twig', [
            'forum' => $forum,
            'sort_by' => $sortBy,
            'submissions' => $submissions,
        ]);
    }

    /**
     * Create a new forum.
     *
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createForumAction(Request $request) {
        $forum = new Forum();

        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $moderator = new Moderator();
            $moderator->setUser($this->getUser());
            $moderator->setForum($forum);
            $forum->addModerator($moderator);

            $subscription = new ForumSubscription();
            $subscription->setForum($forum);
            $subscription->setUser($this->getUser());
            $forum->getSubscriptions()->add($subscription);

            $em = $this->getDoctrine()->getManager();

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
     * @param Request $request
     * @param Forum   $forum
     *
     * @return Response
     */
    public function editForumAction(Request $request, Forum $forum) {
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($form->has('delete') && $form->get('delete')->isClicked()) {
                $em->remove($forum);
                $em->flush();

                $this->addFlash('success', 'flash.forum_deleted');

                return $this->redirectToRoute('raddit_app_front');
            }

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
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request $request
     * @param Forum   $forum   one of 'subscribe' or 'unsubscribe'
     * @param string  $action
     *
     * @return Response
     */
    public function subscribeAction(Request $request, Forum $forum, string $action) {
        if (!$this->isCsrfTokenValid('subscribe', $request->request->get('token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        switch ($action) {
        case 'subscribe':
            if (!$user->getSubscriptionByForum($forum)) {
                $user->addForumSubscription($forum);
            }

            break;
        case 'unsubscribe':
            $subscription = $user->getSubscriptionByForum($forum);

            if ($subscription) {
                $em->remove($subscription);
            }

            break;
        default:
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
     * @param EntityManager $em
     *
     * @return Response
     */
    public function listCategoriesAction(EntityManager $em) {
        $forumCategories = $em->getRepository(ForumCategory::class)->findBy(
            [], ['name' => 'ASC']
        );

        $uncategorizedForums = $em->getRepository(Forum::class)->findBy(
            ['category' => null], ['canonicalName' => 'ASC']
        );

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
     *
     * @return Response
     */
    public function moderatorsAction(Forum $forum) {
        $moderators = $this->getDoctrine()->getRepository(Moderator::class)
            ->findBy(['forum' => $forum], ['id' => 'ASC']);

        return $this->render('@RadditApp/forum_moderators.html.twig', [
            'forum' => $forum,
            'moderators' => $moderators,
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
     * @param Forum   $forum
     * @param Request $request
     *
     * @return Response
     */
    public function addModeratorAction(Forum $forum, Request $request) {
        $moderator = new Moderator();
        $moderator->setForum($forum);

        $form = $this->createForm(ModeratorType::class, $moderator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

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

            return $this->redirectToRoute('raddit_app_forum_apperance', [
                'forum_name' => $forum->getName(),
            ]);
        }

        return $this->render('@RadditApp/forum_appearance.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }
}
