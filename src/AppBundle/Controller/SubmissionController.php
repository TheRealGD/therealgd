<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Form\CommentType;
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
     * @param Submission $submission
     *
     * @return Response
     */
    public function commentPageAction(Submission $submission) {
        return $this->render('@RadditApp/comments.html.twig', [
            'submission' => $submission,
        ]);
    }

    /**
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
     * Show a single comment and its replies. The form created here will reply
     * to that comment.
     *
     * @param Comment $comment
     * @param Request $request
     *
     * @return Response
     */
    public function commentPermalinkAction(Comment $comment, Request $request) {
        $reply = new Comment();

        $form = $this->createForm(CommentType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reply->setSubmission($comment->getSubmission());
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
