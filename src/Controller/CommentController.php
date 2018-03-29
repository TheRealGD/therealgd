<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Forum;
use App\Entity\ForumLogCommentDeletion;
use App\Entity\Submission;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\Model\CommentData;
use App\Repository\CommentRepository;
use App\Repository\ForumRepository;
use App\Utils\Slugger;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Entity("forum", expr="repository.findOneOrRedirectToCanonical(forum_name, 'forum_name')")
 * @Entity("submission", expr="repository.findOneBy({forum: forum, id: submission_id})")
 * @Entity("comment", expr="repository.findOneBy({submission: submission, id: comment_id})")
 */
final class CommentController extends AbstractController {
    public function list(CommentRepository $repository, int $page) {
        // TODO: link this somewhere
        return $this->render('comment/list.html.twig', [
            'comments' => $repository->findRecentPaginated($page),
        ]);
    }

    /**
     * Render the comment form only (no layout).
     *
     * @param ForumRepository $forumRepository
     * @param string          $forumName
     * @param int             $submissionId
     * @param int|null        $commentId
     *
     * @return Response
     */
    public function commentForm(
        ForumRepository $forumRepository,
        $forumName,
        $submissionId,
        $commentId = null
    ) {
        $routeParams = [
            'forum_name' => $forumName,
            'submission_id' => $submissionId,
        ];

        if ($commentId !== null) {
            $routeParams['comment_id'] = $commentId;
        }

        $form = $this->createForm(CommentType::class, null, [
            'action' => $this->generateUrl('comment_post', $routeParams),
            'forum' => $forumRepository->findOneByCaseInsensitiveName($forumName),
        ]);

        return $this->render('comment/form_fragment.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Submit a comment. This is intended for users without JS enabled.
     *
     * @IsGranted("ROLE_USER")
     *
     * @param EntityManager $em
     * @param Forum         $forum
     * @param Submission    $submission
     * @param Request       $request
     * @param Comment|null  $comment
     *
     * @return Response
     */
    public function comment(
        EntityManager $em,
        Forum $forum,
        Submission $submission,
        Request $request,
        Comment $comment = null
    ) {
        $data = new CommentData();

        $form = $this->createForm(CommentType::class, $data, ['forum' => $forum]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $ip = $request->getClientIp();
            $reply = $data->toComment($submission, $user, $comment, $ip);

            $em->persist($reply);
            $em->flush();

            /*
            return $this->redirectToRoute('comment', [
                'forum_name' => $forum->getName(),
                'submission_id' => $submission->getId(),
                'slug' => Slugger::slugify($submission->getTitle()),
            ]);
            */

            if ($reply->getParent() != null){
              return $this->redirectToRoute('comment', [
                  'forum_name' => $forum->getName(),
                  'submission_id' => $submission->getId(),
                  'comment_id' => $reply->getParent()->getId(),
              ]);
            } else {
              return $this->redirectToRoute('submission', [
                  'forum_name' => $forum->getName(),
                  'submission_id' => $submission->getId(),
                  'slug' => Slugger::slugify($submission->getTitle()),
              ]);
            }

        }

        return $this->render('comment/form_errors.html.twig', [
            'editing' => false,
            'form' => $form->createView(),
            'forum' => $forum,
            'submission' => $submission,
            'comment' => $comment,
        ]);
    }

    /**
     * Edits a comment.
     *
     * @IsGranted("edit", subject="comment")
     *
     * @param EntityManager $em
     * @param Forum         $forum
     * @param Submission    $submission
     * @param Comment       $comment
     * @param Request       $request
     *
     * @return Response
     */
    public function editComment(
        EntityManager $em,
        Forum $forum,
        Submission $submission,
        Comment $comment,
        Request $request
    ) {
        $data = CommentData::createFromComment($comment);

        $form = $this->createForm(CommentType::class, $data, ['forum' => $forum]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateComment($comment);

            $em->flush();

            return $this->redirectToRoute('comment', [
                'forum_name' => $forum->getName(),
                'submission_id' => $submission->getId(),
                'comment_id' => $comment->getId(),
            ]);
        }

        return $this->render('comment/form_errors.html.twig', [
            'editing' => true,
            'form' => $form->createView(),
            'forum' => $forum,
            'submission' => $submission,
            'comment' => $comment,
        ]);
    }

    /**
     * Delete a comment.
     *
     * @IsGranted("delete", subject="comment")
     *
     * @param EntityManager $em
     * @param Submission    $submission
     * @param Forum         $forum
     * @param Comment       $comment
     * @param Request       $request
     *
     * @return Response
     */
    public function deleteComment(
        EntityManager $em,
        Submission $submission,
        Forum $forum,
        Comment $comment,
        Request $request
    ) {
        $this->validateCsrf('delete_comment', $request->request->get('token'));

        if ($this->isGranted('delete_thread', $comment)) {
            $em->refresh($comment);
            $em->remove($comment);
        } elseif ($this->isGranted('softdelete', $comment)) {
            $comment->softDelete();
        } else {
            throw new \RuntimeException("This shouldn't happen");
        }

        $this->logDeletion($forum, $comment);

        $commentId = $comment->getId(); // not available on entity after flush()

        $em->flush();

        if ($request->headers->has('Referer')) {
            $commentUrl = $this->generateUrl('comment', [
                'forum_name' => $forum->getName(),
                'submission_id' => $submission->getId(),
                'comment_id' => $commentId,
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            if (strpos($request->headers->get('Referer'), $commentUrl) === 0) {
                // Redirect to original submission comment page.
                return $this->redirectToRoute('submission',[
                  'forum_name' => $forum->getName(),
                  'submission_id' => $submission->getId(),
                  'slug' => Slugger::slugify($submission->getTitle())
                ]);
            }
        }

        return $this->redirectAfterAction($comment, $request);
    }

    /**
     * "Soft deletes" a comment by blanking its body.
     *
     * @IsGranted("softdelete", subject="comment")
     *
     * @param EntityManager $em
     * @param Forum         $forum
     * @param Submission    $submission
     * @param Comment       $comment
     * @param Request       $request
     *
     * @return Response
     */
    public function softDeleteComment(
        EntityManager $em,
        Forum $forum,
        /* @noinspection PhpUnusedParameterInspection */ Submission $submission,
        Comment $comment,
        Request $request
    ) {
        $this->validateCsrf('softdelete_comment', $request->request->get('token'));

        $comment->softDelete();

        $this->logDeletion($forum, $comment);

        $em->flush();

        return $this->redirectAfterAction($comment, $request);
    }

    private function logDeletion(Forum $forum, Comment $comment) {
        /* @var User $user */
        $user = $this->getUser();

        if ($user !== $comment->getUser()) {
            $forum->addLogEntry(new ForumLogCommentDeletion($comment, $user));
        }
    }

    private function redirectAfterAction(Comment $comment, Request $request): Response {
        if ($request->headers->has('Referer')) {
            return $this->redirect($request->headers->get('Referer'));
        }

        return $this->redirectToRoute('submission', [
            'forum_name' => $comment->getSubmission()->getForum()->getName(),
            'submission_id' => $comment->getSubmission()->getId(),
            'slug' => Slugger::slugify($comment->getSubmission()->getTitle()),
        ]);
    }
}
