<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Entity\Moderator;
use App\Entity\User;
use App\Form\ForumAppearanceType;
use App\Form\ForumBanType;
use App\Form\ForumType;
use App\Form\Model\ForumBanData;
use App\Form\Model\ForumData;
use App\Form\Model\ModeratorData;
use App\Form\ModeratorType;
use App\Form\PasswordConfirmType;
use App\Repository\ForumBanRepository;
use App\Repository\ForumCategoryRepository;
use App\Repository\ForumLogEntryRepository;
use App\Repository\ForumRepository;
use App\Repository\SubmissionRepository;
use App\Utils\PermissionsChecker;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Entity("forum", expr="repository.findOneOrRedirectToCanonical(forum_name, 'forum_name')")
 * @Entity("user", expr="repository.findOneOrRedirectToCanonical(username, 'username')")
 */
final class ForumController extends AbstractController {
    /**
     * Show the front page of a given forum.
     *
     * @param SubmissionRepository $sr
     * @param Forum                $forum
     * @param string               $sortBy
     * @param int                  $page
     *
     * @return Response
     */
    public function front(SubmissionRepository $sr, Forum $forum, string $sortBy, int $page) {
        if (PermissionsChecker::isAdmin($this->getUser())) {
            return $this->redirectToRoute('login');
        }
        $submissions = $sr->findForumSubmissions($forum, $sortBy, $page);

        return $this->render('forum/forum.html.twig', [
            'forum' => $forum,
            'sort_by' => $sortBy,
            'submissions' => $submissions,
        ]);
    }

    /**
     * @IsGranted("moderator", subject="forum")
     * Show the list of mod posts of a given forum.
     *
     * @param SubmissionRepository $sr
     * @param Forum                $forum
     * @param string               $sortBy
     * @param int                  $page
     *
     * @return Response
     */
    public function modFront(SubmissionRepository $sr, Forum $forum, string $sortBy, int $page) {
        $submissions = $sr->findModForumSubmissions($forum, $sortBy, $page);

        return $this->render('forum/forum.html.twig', [
            'forum' => $forum,
            'sort_by' => $sortBy,
            'submissions' => $submissions,
            'mod' => true
        ]);
    }

    public function multi(ForumRepository $fr, SubmissionRepository $sr,
                                string $names, string $sortBy, int $page) {
        $names = preg_split('/[^\w]+/', $names, -1, PREG_SPLIT_NO_EMPTY);
        $names = array_map(Forum::class.'::normalizeName', $names);
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
     * @IsGranted("create_forum")
     *
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function createForum(Request $request, EntityManager $em) {
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
     * @IsGranted("moderator", subject="forum")
     *
     * @param Request       $request
     * @param Forum         $forum
     * @param EntityManager $em
     *
     * @return Response
     */
    public function editForum(Request $request, Forum $forum, EntityManager $em) {
        $data = ForumData::createFromForum($forum);

        $form = $this->createForm(ForumType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateForum($forum);

            $em->flush();

            $this->addFlash('success', 'flash.forum_updated');

            return $this->redirectToRoute('edit_forum', [
                'forum_name' => $forum->getName(),
            ]);
        }

        return $this->render('forum/edit.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }

    /**
     * @param Forum                $forum
     * @param SubmissionRepository $sr
     * @param string               $sortBy
     * @param int                  $page
     *
     * @return Response
     */
    public function feed(Forum $forum, SubmissionRepository $sr, string $sortBy, int $page) {
        return $this->render('forum/feed.xml.twig', [
            'forum' => $forum,
            'submissions' => $sr->findForumSubmissions($forum, $sortBy, $page),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request       $request
     * @param Forum         $forum
     * @param EntityManager $em
     *
     * @return Response
     */
    public function delete(Request $request, Forum $forum, EntityManager $em) {
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
     * @IsGranted("ROLE_USER")
     *
     * @param Request       $request
     * @param EntityManager $em
     * @param Forum         $forum
     * @param bool          $subscribe
     * @param string        $_format
     *
     * @return Response
     */
    public function subscribe(Request $request, EntityManager $em, Forum $forum, bool $subscribe, string $_format) {
        $this->validateCsrf('subscribe', $request->request->get('token'));

        if ($subscribe) {
            $forum->subscribe($this->getUser());
        } else {
            $forum->unsubscribe($this->getUser());
        }

        $em->flush();

        if ($_format === 'json') {
            return $this->json(['subscribed' => $subscribe]);
        }

        if ($request->headers->has('Referer')) {
            return $this->redirect($request->headers->get('Referer'));
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
    public function list(ForumRepository $repository, int $page = 1, string $sortBy) {
        return $this->render('forum/list.html.twig', [
            'forums' => $repository->findForumsByPage($page, $sortBy, $this->getUser()),
            'sortBy' => $sortBy,
        ]);
    }

    /**
     * @param ForumCategoryRepository $fcr
     * @param ForumRepository         $fr
     *
     * @return Response
     */
    public function listCategories(ForumCategoryRepository $fcr, ForumRepository $fr) {
        $modForumCategory = $fr->getModForumCategory();
        $isAdmin = PermissionsChecker::isAdmin($this->getUser());
        $forumCategories = $fcr->findCategories($isAdmin, $modForumCategory);
        $uncategorizedForums = $fr->findUncategorizedForums($isAdmin);

        return $this->render('forum/list_by_category.html.twig', [
            'forum_categories' => $forumCategories,
            'uncategorized_forums' => $uncategorizedForums,
        ]);
    }

    /**
     * Show a list of forum moderators.
     *
     * @param Forum $forum
     * @param int   $page
     *
     * @return Response
     */
    public function moderators(Forum $forum, int $page) {
        return $this->render('forum/moderators.html.twig', [
            'forum' => $forum,
            'moderators' => $forum->getPaginatedModerators($page),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * @param EntityManager $em
     * @param Forum         $forum
     * @param Request       $request
     *
     * @return Response
     */
    public function addModerator(EntityManager $em, Forum $forum, Request $request) {
        $data = new ModeratorData($forum);
        $form = $this->createForm(ModeratorType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $forum->addModerator($data->toModerator());

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
     * @Entity("moderator", expr="repository.findOneBy({'forum': forum, 'id': moderator_id})")
     * @IsGranted("remove", subject="moderator")
     *
     * @param EntityManager $em
     * @param Forum         $forum
     * @param Request       $request
     * @param Moderator     $moderator
     *
     * @return Response
     */
    public function removeModerator(EntityManager $em, Forum $forum, Request $request, Moderator $moderator) {
        $this->validateCsrf('remove_moderator', $request->request->get('token'));

        $em->remove($moderator);
        $em->flush();

        $this->addFlash('success', 'flash.user_unmodded');

        return $this->redirectToRoute('forum_moderators', [
            'forum_name' => $forum->getName(),
        ]);
    }

    public function moderationLog(Forum $forum, int $page) {
        return $this->render('forum/moderation_log.html.twig', [
            'forum' => $forum,
            'logs' => $forum->getPaginatedLogEntries($page),
        ]);
    }

    public function globalModerationLog(ForumLogEntryRepository $forumLogs, int $page) {
        return $this->render('forum/global_moderation_log.html.twig', [
            'logs' => $forumLogs->findAllPaginated($page),
        ]);
    }

    /**
     * Alter a forum's appearance.
     *
     * @IsGranted("moderator", subject="forum")
     *
     * @param Forum         $forum
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function appearance(Forum $forum, Request $request, EntityManager $em) {
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
     * @param Forum              $forum
     * @param ForumBanRepository $banRepository
     * @param int                $page
     *
     * @return Response
     */
    public function bans(Forum $forum, ForumBanRepository $banRepository, int $page = 1) {
        return $this->render('forum/bans.html.twig', [
            'bans' => $banRepository->findValidBansInForum($forum, $page),
            'forum' => $forum,
        ]);
    }

    /**
     * @IsGranted("moderator", subject="forum")
     *
     * @param Forum $forum
     * @param User  $user
     * @param int   $page
     *
     * @return Response
     */
    public function banHistory(Forum $forum, User $user, int $page = 1) {
        return $this->render('forum/ban_history.html.twig', [
            'bans' => $forum->getPaginatedBansByUser($user, $page),
            'forum' => $forum,
            'user' => $user,
        ]);
    }

    /**
     * @IsGranted("moderator", subject="forum")
     *
     * @param Forum         $forum
     * @param User          $user
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function ban(Forum $forum, User $user, Request $request, EntityManager $em) {
        $data = new ForumBanData();

        $form = $this->createForm(ForumBanType::class, $data, ['intent' => 'ban']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $forum->addBan($data->toBan($forum, $user, $this->getUser()));

            $em->flush();

            $this->addFlash('success', 'flash.user_was_banned');

            return $this->redirectToRoute('forum_bans', [
                'forum_name' => $forum->getName(),
            ]);
        }

        return $this->render('forum/ban.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
            'user' => $user,
        ]);
    }

    /**
     * @IsGranted("moderator", subject="forum")
     *
     * @param Forum         $forum
     * @param User          $user
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function unban(Forum $forum, User $user, Request $request, EntityManager $em) {
        $data = new ForumBanData();

        $form = $this->createForm(ForumBanType::class, $data, ['intent' => 'unban']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $forum->addBan($data->toUnban($forum, $user, $this->getUser()));

            $em->flush();

            $this->addFlash('success', 'flash.user_was_unbanned');

            return $this->redirectToRoute('forum_bans', [
                'forum_name' => $forum->getName(),
            ]);
        }

        return $this->render('forum/unban.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
            'user' => $user,
        ]);
    }
}

