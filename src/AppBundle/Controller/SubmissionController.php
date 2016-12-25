<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SubmissionController extends Controller {
    /**
     * View submissions on the front page.
     *
     * @return Response
     */
    public function frontPageAction() {
        $submissions = $this->getDoctrine()->getRepository(Submission::class)
            ->findBy([], ['id' => 'DESC'], 20);

        return $this->render('@RadditApp/front.html.twig', [
            'submissions' => $submissions,
        ]);
    }

    /**
     * Show the front page of a given forum.
     *
     * @param Forum $forum
     *
     * @return Response
     */
    public function forumAction(Forum $forum) {
        $submissions = $this->getDoctrine()->getRepository(Submission::class)
            ->findBy(['forum' => $forum], ['id' => 'DESC'], 20);

        return $this->render('@RadditApp/forum.html.twig', [
            'forum' => $forum,
            'submissions' => $submissions,
        ]);
    }

    /**
     * Show a submission's comment page.
     *
     * @ParamConverter("forum", options={"mapping": {"forum_name": "name"}})
     * @ParamConverter("submission", options={"mapping": {"forum": "forum", "submission_id": "id"}})
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
     * @ParamConverter("forum", options={"mapping": {"forum_name": "name"}})
     * @ParamConverter("submission", options={"mapping": {"forum": "forum", "submission_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"submission": "submission", "comment_id": "id"}})
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
     * @param string  $typeClass
     * @param string  $entityClass
     *
     * @return Response
     */
    public function submitAction(Forum $forum, Request $request, $typeClass, $entityClass) {
        /** @var Submission $submission */
        /** @noinspection PhpUndefinedMethodInspection */
        $submission = $entityClass::create($forum, $this->getUser());

        $form = $this->createForm($typeClass, $submission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($submission);
            $em->flush();

            return $this->redirectToRoute('raddit_app_comments', [
                'forum_name' => $forum->getName(),
                'submission_id' => $submission->getId(),
            ]);
        }

        return $this->render('@RadditApp/submit.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
            'submission_type' => $submission->getSubmissionType(),
        ]);
    }
}
