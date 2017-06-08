<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Form\SubmissionType;
use Raddit\AppBundle\Utils\Slugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        return $this->render('@RadditApp/comments.html.twig', [
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
        return $this->render('@RadditApp/comment.html.twig', [
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
     * @param Forum   $forum
     * @param Request $request
     *
     * @return Response
     */
    public function submitAction(Request $request, Forum $forum = null) {
        $submission = Submission::create($forum, $this->getUser());

        $form = $this->createForm(SubmissionType::class, $submission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($submission);
            $em->flush();

            return $this->redirectToRoute('raddit_app_comments', [
                'forum_name' => $submission->getForum()->getName(),
                'submission_id' => $submission->getId(),
                'slug' => Slugger::slugify($submission->getTitle()),
            ]);
        }

        return $this->render('@RadditApp/submit.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }

    /**
     * @Security("is_granted('edit', submission)")
     *
     * @param Forum      $forum
     * @param Submission $submission
     * @param Request    $request
     *
     * @return Response
     */
    public function editSubmissionAction(Forum $forum, Submission $submission, Request $request) {
        $form = $this->createForm(SubmissionType::class, $submission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($form->get('delete')->isClicked()) {
                $em->remove($submission);
                $em->flush();

                $this->addFlash('notice', 'submissions.delete_notice');

                return $this->redirectToRoute('raddit_app_forum', [
                    'forum_name' => $forum->getName(),
                ]);
            }

            $this->addFlash('notice', 'submissions.edit_notice');

            $em->flush();

            return $this->redirectToRoute('raddit_app_comments', [
                'forum_name' => $forum->getName(),
                'submission_id' => $submission->getId(),
                'slug' => Slugger::slugify($submission->getTitle()),
            ]);
        }

        return $this->render('@RadditApp/submit.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
            'submission' => $submission,
        ]);
    }
}
