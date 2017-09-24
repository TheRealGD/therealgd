<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Form\Model\SubmissionData;
use Raddit\AppBundle\Form\SubmissionType;
use Raddit\AppBundle\Utils\Slugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @ParamConverter("forum", options={
 *     "mapping": {"forum_name": "name"},
 *     "map_method_signature": true,
 *     "repository_method": "findOneByCaseInsensitiveName"
 * })
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
    public function commentPageAction(Forum $forum, Submission $submission) {
        return $this->render('@RadditApp/submission/comments.html.twig', [
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
    public function commentPermalinkAction(
        Forum $forum,
        Submission $submission,
        Comment $comment
    ) {
        return $this->render('@RadditApp/submission/comment.html.twig', [
            'comment' => $comment,
            'forum' => $forum,
            'submission' => $submission,
        ]);
    }

    /**
     * Create a new submission.
     *
     * @Security("is_granted('ROLE_USER')")
     *
     * @param EntityManager $em
     * @param Request       $request
     * @param Forum         $forum
     *
     * @return Response
     */
    public function submitAction(EntityManager $em, Request $request, Forum $forum = null) {
        $data = new SubmissionData($forum);

        $form = $this->createForm(SubmissionType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submission = $data->toSubmission($this->getUser(), $request->getClientIp());

            $em->persist($submission);
            $em->flush();

            return $this->redirectToRoute('raddit_app_comments', [
                'forum_name' => $submission->getForum()->getName(),
                'submission_id' => $submission->getId(),
                'slug' => Slugger::slugify($submission->getTitle()),
            ]);
        }

        return $this->render('@RadditApp/submission/create.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }

    /**
     * @Security("is_granted('edit', submission)")
     *
     * @param EntityManager $em
     * @param Forum         $forum
     * @param Submission    $submission
     * @param Request       $request
     *
     * @return Response
     */
    public function editSubmissionAction(EntityManager $em, Forum $forum, Submission $submission, Request $request) {
        $data = SubmissionData::createFromSubmission($submission);

        $form = $this->createForm(SubmissionType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $em->remove($submission);
                $em->flush();

                $this->addFlash('notice', 'flash.submission_deleted');

                return $this->redirectToRoute('raddit_app_forum', [
                    'forum_name' => $forum->getName(),
                ]);
            }

            $data->updateSubmission($submission);

            $em->flush();

            $this->addFlash('notice', 'flash.submission_edited');

            return $this->redirectToRoute('raddit_app_comments', [
                'forum_name' => $forum->getName(),
                'submission_id' => $submission->getId(),
                'slug' => Slugger::slugify($submission->getTitle()),
            ]);
        }

        return $this->render('@RadditApp/submission/edit.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
            'submission' => $submission,
        ]);
    }

    /**
     * @Security("is_granted('moderator', forum)")
     *
     * @param EntityManager $em
     * @param Request       $request
     * @param Forum         $forum
     * @param Submission    $submission
     * @param bool          $lock
     *
     * @return Response
     */
    public function lockAction(
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

        return $this->redirectToRoute('raddit_app_comments', [
            'forum_name' => $forum->getName(),
            'submission_id' => $submission->getId(),
            'slug' => Slugger::slugify($submission->getTitle()),
        ]);
    }
}
