<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Forum;
use App\Entity\ForumLogCommentDeletion;
use App\Entity\Submission;
use App\Entity\User;
use App\Entity\Report;
use App\Entity\ReportEntry;
use App\Form\CommentType;
use App\Form\Model\CommentData;
use App\Repository\CommentRepository;
use App\Repository\ForumRepository;
use App\Repository\ReportRepository;
use App\Utils\Slugger;
use App\Utils\ReportHelper;
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

            // Nested comment - go to parent comment
            if ($reply->getParent() !== null) {
              return $this->redirectToRoute('comment', [
                  'forum_name' => $forum->getName(),
                  'submission_id' => $submission->getId(),
                  'comment_id' => $reply->getParent()->getId(),
              ]);
            }
            // By default - go to post
            return $this->redirectToRoute('submission', [
                'forum_name' => $forum->getName(),
                'submission_id' => $submission->getId(),
                'slug' => Slugger::slugify($submission->getTitle()),
            ]);
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
     * Reports a comment.
     *
     * @IsGranted("ROLE_USER")
     *
     * @param EntityManager $em
     * @param Submission    $submission
     * @param Forum         $forum
     * @param Comment       $comment
     * @param Request       $request
     *
     * @return Response
     */
    public function reportComment(
        EntityManager $em,
        Submission $submission,
        Forum $forum,
        Comment $comment,
        Request $request,
        ReportRepository $rr
    ) {
        $this->validateCsrf('report_comment', $request->request->get('token'));

        $reportBody = trim($request->request->get('reportBody'));
        $success = false;

        if(!empty($reportBody)) {
            $comment->incrementReportCount();
            $em->persist($comment);

            // Find a report for this comment. If it doesn't exist, create it.
            $report = $rr->findOneByComment($comment);
            if(!$report) {
                $report = new Report();
                $report->setComment($comment);
                $report->setForum($forum);
                $em->persist($report);
                $em->flush();
            } else if($report->getIsResolved()) {
                // Reset the isResolved flag if it is re-reported and remove all previous reports.
                foreach($report->getEntries() as $entry) { $em->remove($entry); }
                $em->flush();

                $em->refresh($report);
                $report->setIsResolved(false);

                $em->persist($report);
            }

            // Add the report entry to the report.
            $entry = new ReportEntry();
            $entry->setReport($report);
            $entry->setUser($this->getUser());
            $entry->setBody($reportBody);
            $em->persist($entry);
            $em->flush();

            $success = true;
        }

        return $this->JSONResponse(array("success" => $success));
    }

    /**
     * Get the entries for a given comment.
     *
     * @IsGranted("moderator", subject="forum")
     *
     * @param EntityManager $em
     * @param Submission    $submission
     * @param Forum         $forum
     * @param Comment       $comment
     * @param Request       $request
     *
     * @return Response
     */
    public function reportEntries(
        EntityManager $em,
        Submission $submission,
        Forum $forum,
        Comment $comment,
        Request $request,
        ReportRepository $rr
    ) {
        $result = [];
        $report = $rr->findOneByComment($comment);

        if($report) {
            foreach($report->getEntries() as $entry) {
                $result[] = array("body" => $entry->getBody());
            }
        }

        return $this->JSONResponse($result);
    }

    /**
     * Process a report action for a given comment..
     *
     * @IsGranted("moderator", subject="forum")
     *
     * @param EntityManager $em
     * @param Submission    $submission
     * @param Forum         $forum
     * @param Comment       $comment
     * @param Request       $request
     *
     * @return Response
     */
    public function reportAction(
        EntityManager $em,
        Submission $submission,
        Forum $forum,
        Comment $comment,
        Request $request,
        ReportRepository $rr
    ) {
        $action = $request->request->get('reportAction');
        $report = $rr->findOneByComment($comment);
        $success = false;

        if($report) {
            // Removal action.
            if($action == "remove") {
                $report->setIsResolved(true);
                $em->persist($report);

                $comment->setSoftDeleted(true);
                $comment->setReportCount(0);
                $em->persist($comment);

                $success = true;
                $em->flush();
            }

            // Approval action.
            if($action == "approve") {
                $report->setIsResolved(true);
                $em->persist($report);

                $comment->setReportCount(0);
                $em->persist($comment);

                $success = true;
                $em->flush();
            }
        }

        return $this->JSONResponse(array("success" => $success));
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

    /**
     *
     * @IsGranted("moderator", subject="forum")
     * @todo - updated is granted to being a forum mod or role_admin, possibly need to use security annotation
     *
     * @param EntityManager $em
     * @param Forum         $forum
     * @param Submission    $submission
     * @param Comment       $comment
     * @param Request       $request
     *
     * @return Response
     */
    public function stickyComment(
        EntityManager $em,
        Forum $forum,
        Submission $submission,
        Comment $comment,
        Request $request

    ) {
        $comment->setStickied(true);
        $em->flush();

        return $this->redirectAfterAction($comment, $request);
    }

    /**
     *
     * @IsGranted("moderator", subject="forum")
     * @todo - updated is granted to being a forum mod or role_admin, possibly need to use security annotation
     *
     * @param EntityManager $em
     * @param Forum         $forum
     * @param Submission    $submission
     * @param Comment       $comment
     * @param Request       $request
     *
     * @return Response
     */
    public function unstickyComment(
        EntityManager $em,
        Forum $forum,
        Submission $submission,
        Comment $comment,
        Request $request

    ) {
        $comment->setStickied(false);
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
