<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Comment;
use AppBundle\Entity\Forum;
use AppBundle\Entity\ForumLogSubmissionDeletion;
use AppBundle\Entity\Submission;
use AppBundle\Entity\User;
use AppBundle\Form\Model\SubmissionData;
use AppBundle\Form\SubmissionType;
use AppBundle\Utils\Slugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Entity("forum", expr="repository.findOneByCaseInsensitiveName(forum_name)")
 * @ParamConverter("submission", options={"mapping": {"forum": "forum", "submission_id": "id"}})
 * @ParamConverter("comment", options={"mapping": {"submission": "submission", "comment_id": "id"}})
 */
final class SubmissionController extends AbstractController {
    /**
     * Show a submission's comment page.
     *
     * @param Forum      $forum
     * @param Submission $submission
     *
     * @return Response
     */
    public function commentPage(Forum $forum, Submission $submission) {
        return $this->render('submission/comments.html.twig', [
            'forum' => $forum,
            'submission' => $submission,
        ]);
    }

    /**
     * Show a single comment and its replies.
     *
     * @param Forum      $forum
     * @param Submission $submission
     * @param Comment    $comment
     *
     * @return Response
     */
    public function commentPermalink(
        Forum $forum,
        Submission $submission,
        Comment $comment
    ) {
        return $this->render('submission/comment.html.twig', [
            'comment' => $comment,
            'forum' => $forum,
            'submission' => $submission,
        ]);
    }

    /**
     * Create a new submission.
     *
     * @IsGranted("ROLE_USER")
     *
     * @param EntityManager $em
     * @param Request       $request
     * @param Forum         $forum
     *
     * @return Response
     */
    public function submit(EntityManager $em, Request $request, Forum $forum = null) {
        $data = new SubmissionData($forum);

        $form = $this->createForm(SubmissionType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submission = $data->toSubmission($this->getUser(), $request->getClientIp());

            $em->persist($submission);
            $em->flush();

            return $this->redirectToRoute('comments', [
                'forum_name' => $submission->getForum()->getName(),
                'submission_id' => $submission->getId(),
                'slug' => Slugger::slugify($submission->getTitle()),
            ]);
        }

        return $this->render('submission/create.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }

    /**
     * @IsGranted("edit", subject="submission")
     *
     * @param EntityManager $em
     * @param Forum         $forum
     * @param Submission    $submission
     * @param Request       $request
     *
     * @return Response
     */
    public function editSubmission(EntityManager $em, Forum $forum, Submission $submission, Request $request) {
        $data = SubmissionData::createFromSubmission($submission);

        $form = $this->createForm(SubmissionType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateSubmission($submission);

            $em->flush();

            $this->addFlash('notice', 'flash.submission_edited');

            return $this->redirectToRoute('comments', [
                'forum_name' => $forum->getName(),
                'submission_id' => $submission->getId(),
                'slug' => Slugger::slugify($submission->getTitle()),
            ]);
        }

        return $this->render('submission/edit.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
            'submission' => $submission,
        ]);
    }

    /**
     * @IsGranted("edit", subject="submission")
     *
     * @param Request       $request
     * @param EntityManager $em
     * @param Forum         $forum
     * @param Submission    $submission
     *
     * @return Response
     */
    public function deleteSubmission(Request $request, EntityManager $em, Forum $forum, Submission $submission) {
        $this->validateCsrf('delete_submission', $request->request->get('token'));

        $em->refresh($submission);
        $em->remove($submission);

        /* @var User $user */
        $user = $this->getUser();

        if ($user !== $submission->getUser()) {
            $forum->addLogEntry(new ForumLogSubmissionDeletion(
                $forum,
                $user,
                !$forum->userIsModerator($user, false),
                $submission->getTitle(),
                $submission->getUser()
            ));
        }

        $em->flush();

        $this->addFlash('notice', 'flash.submission_deleted');

        if ($request->headers->has('Referer')) {
            return $this->redirect($request->headers->get('Referer'));
        }

        return $this->redirectToRoute('forum', ['forum_name' => $forum->getName()]);
    }

    /**
     * @IsGranted("moderator", subject="forum")
     *
     * @param EntityManager $em
     * @param Request       $request
     * @param Forum         $forum
     * @param Submission    $submission
     * @param bool          $lock
     *
     * @return Response
     */
    public function lock(
        EntityManager $em,
        Request $request,
        Forum $forum,
        Submission $submission,
        bool $lock
    ) {
        $this->validateCsrf('lock', $request->request->get('token'));

        $submission->setLocked($lock);
        $em->flush();

        if ($lock) {
            $this->addFlash('success', 'flash.submission_locked');
        } else {
            $this->addFlash('success', 'flash.submission_unlocked');
        }

        if ($request->headers->has('Referer')) {
            return $this->redirect($request->headers->get('Referer'));
        }

        return $this->redirectToRoute('comments', [
            'forum_name' => $forum->getName(),
            'submission_id' => $submission->getId(),
            'slug' => Slugger::slugify($submission->getTitle()),
        ]);
    }
}
