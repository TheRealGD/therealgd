<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Form\CommentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
            'submission' => $submission,
        ]);
    }

    /**
     * Show a single comment and its replies. The form created here will reply
     * to that comment.
     *
     * @ParamConverter("forum", options={"mapping": {"forum_name": "name"}})
     * @ParamConverter("submission", options={"mapping": {"forum": "forum", "submission_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"submission": "submission", "comment_id": "id"}})
     *
     * @param Forum      $forum
     * @param Submission $submission
     * @param Comment    $comment
     * @param Request    $request
     *
     * @return Response
     */
    public function commentPermalinkAction(
        Forum $forum,
        Submission $submission,
        Comment $comment,
        Request $request
    ) {
        $reply = new Comment();

        $form = $this->createForm(CommentType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reply->setSubmission($submission);
            $reply->setParent($comment);
            $reply->setUser($this->getUser());

            $em = $this->getDoctrine()->getManager();

            $em->persist($reply);
            $em->flush();
        }

        return $this->render('comment.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }
}
