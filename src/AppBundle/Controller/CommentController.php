<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Form\CommentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ParamConverter("forum", options={"mapping": {"forum_name": "name"}})
 * @ParamConverter("submission", options={"mapping": {"forum": "forum", "submission_id": "id"}})
 * @ParamConverter("comment", options={"mapping": {"submission": "submission", "comment_id": "id"}})
 */
final class CommentController extends Controller {
    /**
     * Render the comment form only (no layout).
     *
     * @param string   $forumName
     * @param int      $submissionId
     * @param int|null $commentId
     *
     * @return Response
     */
    public function commentFormAction($forumName, $submissionId, $commentId = null) {
        $routeParams = [
            'forum_name' => $forumName,
            'submission_id' => $submissionId,
        ];

        if ($commentId !== null) {
            $routeParams['comment_id'] = $commentId;
        }

        $form = $this->createForm(CommentType::class, null, [
            'action' => $this->generateUrl('raddit_app_comment_post', $routeParams),
        ]);

        return $this->render('@RadditApp/fragments/comment-form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Submit a comment. This is intended for users without JS enabled.
     *
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Forum        $forum
     * @param Submission   $submission
     * @param Request      $request
     * @param Comment|null $comment
     *
     * @return Response
     */
    public function commentAction(
        Forum $forum,
        Submission $submission,
        Request $request,
        Comment $comment = null
    ) {
        $reply = Comment::create($submission, $this->getUser(), $comment);

        $form = $this->createForm(CommentType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($reply);
            $em->flush();

            return $this->redirectToRoute('raddit_app_comment', [
                'forum_name' => $forum->getName(),
                'submission_id' => $submission->getId(),
                'comment_id' => $reply->getId(),
            ]);
        }

        return $this->render('@RadditApp/comment-form-errors.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
            'submission' => $submission,
            'comment' => $comment,
        ]);
    }

    /**
     * Delete a comment.
     *
     * @ParamConverter("comment", options={"mapping": {"id": "id"}})
     * @Security("is_granted('delete', comment)")
     *
     * @param Comment $comment
     * @param Request $request
     *
     * @return Response
     */
    public function deleteCommentAction(Comment $comment, Request $request) {
        if (!$this->isCsrfTokenValid('delete_comment', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Bad CSRF token');
        }

        $em = $this->getDoctrine()->getManager();

        if ($this->isGranted('delete_thread', $comment)) {
            $em->refresh($comment);
            $em->remove($comment);
        } elseif ($this->isGranted('softdelete', $comment)) {
            $comment->softDelete();
        } else {
            throw new \RuntimeException("This shouldn't happen");
        }

        $em->flush();

        return $this->redirectAfterAction($comment, $request);
    }

    /**
     * "Soft deletes" a comment by blanking its body.
     *
     * @ParamConverter("comment", options={"mapping": {"id": "id"}})
     * @Security("is_granted('softdelete', comment)")
     *
     * @param Comment $comment
     * @param Request $request
     *
     * @return Response
     */
    public function softDeleteCommentAction(Comment $comment, Request $request) {
        if (!$this->isCsrfTokenValid('softdelete_comment', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Bad CSRF token');
        }

        $comment->softDelete();

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->redirectAfterAction($comment, $request);
    }

    /**
     * @param Comment $comment
     * @param Request $request
     *
     * @return Response
     */
    private function redirectAfterAction(Comment $comment, Request $request) {
        if ($request->headers->has('Referer')) {
            return $this->redirect($request->headers->get('Referer'));
        }

        return $this->redirectToRoute('raddit_app_comments', [
            'forum_name' => $comment->getSubmission()->getForum()->getName(),
            'submission_id' => $comment->getSubmission()->getId(),
        ]);
    }
}
