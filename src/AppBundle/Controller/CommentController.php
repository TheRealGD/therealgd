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
     * @ParamConverter("forum", options={"mapping": {"forum_name": "name"}})
     * @ParamConverter("submission", options={
     *     "mapping": {"forum": "forum", "submission_id": "id"}
     * })
     * @ParamConverter("comment", options={
     *     "mapping": {"submission": "submission", "comment_id": "id"}
     * })
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
}
