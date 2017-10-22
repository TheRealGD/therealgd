<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\ForumLogSubmissionDeletion;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Form\Model\SubmissionData;
use Raddit\AppBundle\Form\SubmissionType;
use Raddit\AppBundle\Utils\Slugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Entity("forum", expr="repository.findOneByCaseInsensitiveName(forum_name)")
 * @ParamConverter("submission", options={"mapping": {"forum": "forum", "submission_id": "id"}})
 * @ParamConverter("comment", options={"mapping": {"submission": "submission", "comment_id": "id"}})
 */
final class SubmissionController extends Controller {
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
        if (!$this->isCsrfTokenValid('delete_submission', $request->request->get('token'))) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }

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
        if (!$this->isCsrfTokenValid('lock', $request->request->get('token'))) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }

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
