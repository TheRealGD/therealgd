<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Forum;
use App\Entity\ForumLogSubmissionDeletion;
use App\Entity\ForumLogSubmissionLock;
use App\Entity\Submission;
use App\Entity\Report;
use App\Entity\ReportEntry;
use App\Form\DeleteReasonType;
use App\Form\Model\SubmissionData;
use App\Form\SubmissionType;
use App\Repository\SubmissionRepository;
use App\Repository\ForumRepository;
use App\Repository\UserRepository;
use App\Repository\ReportRepository;
use App\Repository\RateLimitRepository;
use App\Utils\Slugger;
use App\Utils\ReportHelper;
use App\Utils\PermissionsChecker;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;

/**
 * @Entity("forum", expr="repository.findOneOrRedirectToCanonical(forum_name, 'forum_name')")
 * @Entity("submission", expr="repository.findOneBy({forum: forum, id: submission_id})")
 * @Entity("comment", expr="repository.findOneBy({submission: submission, id: comment_id})")
 */
final class SubmissionController extends AbstractController {
    /**
     * Show a submission's comment page.
     *
     * @param Forum      $forum
     * @param Submission $submission
     *
     * @return Response
     */
    public function submission(Forum $forum, Submission $submission, Request $request) {
        return $this->render('submission/submission.html.twig', [
            'forum' => $forum,
            'referer' => $request->headers->get('Referer'),
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
        if (!$this->hasRightsToViewForum($forum)) { return $this->rerouteAwayFromAdmin(); }
        return $this->render('submission/comment.html.twig', [
            'comment' => $comment,
            'forum' => $forum,
            'submission' => $submission,
        ]);
    }

    /**
     * @Entity("submission", expr="repository.find(id)")
     *
     * @param Submission $submission
     *
     * @return Response
     */
    public function shortcut(Submission $submission) {
        if (!$this->hasRightsToViewForum($forum)) { return $this->rerouteAwayFromAdmin(); }
        return $this->redirectToRoute('submission', [
            'forum_name' => $submission->getForum()->getName(),
            'submission_id' => $submission->getId(),
            'slug' => Slugger::slugify($submission->getTitle()),
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
    public function submit(SubmissionRepository $sr, RateLimitRepository $rlr, EntityManager $em, Request $request, Forum $forum = null) {
        $user = $this->getUser();
        $data = new SubmissionData($forum);

        $form = $this->createForm(SubmissionType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submission = $data->toSubmission($this->getUser(), $request->getClientIp());
            $forum = $submission->getForum();

            $em->persist($submission);

            $lastPost = $sr->getLastPostByUser($user, $forum);
            $violations = $rlr->verifyPost($user, $forum, $submission, $lastPost);
            if ($violations !== false) {
                foreach ($violations as $violation) {
                    $em->persist($violation);
                }
            }

            $em->flush();

            return $this->redirectToRoute('submission', [
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
     * Create a new submission.
     *
     * @IsGranted("moderator", subject="forum")
     *
     * @param EntityManager $em
     * @param Request       $request
     * @param Forum         $forum
     *
     * @return Response
     */
    public function modSubmit(EntityManager $em, Request $request, Forum $forum = null) {
        $data = new SubmissionData($forum);
        $form = $this->createForm(SubmissionType::class, $data, array('user' => $this->getUser(), 'is_mod_submit' => true));
        $form->handleRequest($request);
        $data->setForum($forum);
        if ($form->isSubmitted() && $form->isValid()) {
            $submission = $data->toSubmission($this->getUser(), $request->getClientIp(), true /* $modThread */);
            $em->persist($submission);
            $em->flush();
            return $this->redirectToRoute('submission', [
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

            return $this->redirectToRoute('submission', [
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
     * @IsGranted("delete_with_reason", subject="submission")
     *
     * @param Request       $request
     * @param EntityManager $em
     * @param Forum         $forum
     * @param Submission    $submission
     *
     * @return Response
     */
    public function deleteWithReason(Request $request, EntityManager $em, Forum $forum, Submission $submission) {
        $form = $this->createForm(DeleteReasonType::class, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->refresh($submission);
            $em->remove($submission);

            $forum->addLogEntry(new ForumLogSubmissionDeletion(
                $submission,
                $this->getUser(),
                $form->getData()['reason']
            ));

            $em->flush();

            $this->addFlash('notice', 'flash.submission_deleted');

            return $this->redirectToRoute('forum', [
                'forum_name' => $forum->getName(),
            ]);
        }

        return $this->render('submission/delete_with_reason.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
            'submission' => $submission,
        ]);
    }

    /**
     * @IsGranted("delete_immediately", subject="submission")
     *
     * @param Request       $request
     * @param EntityManager $em
     * @param Forum         $forum
     * @param Submission    $submission
     *
     * @return Response
     */
    public function deleteImmediately(Request $request, EntityManager $em, Forum $forum, Submission $submission) {
        $this->validateCsrf('delete_submission', $request->request->get('token'));

        $em->refresh($submission);
        $em->remove($submission);
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
        $this->validateCsrf('lock', $request->request->get('token'));

        $submission->setLocked($lock);

        $em->persist(new ForumLogSubmissionLock($submission, $this->getUser(), $lock));
        $em->flush();

        if ($lock) {
            $this->addFlash('success', 'flash.submission_locked');
        } else {
            $this->addFlash('success', 'flash.submission_unlocked');
        }

        if ($request->headers->has('Referer')) {
            return $this->redirect($request->headers->get('Referer'));
        }

        return $this->redirectToRoute('submission', [
            'forum_name' => $forum->getName(),
            'submission_id' => $submission->getId(),
            'slug' => Slugger::slugify($submission->getTitle()),
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     *
     * @param EntityManager $em
     * @param Request       $request
     * @param Forum         $forum
     * @param Submission    $submission
     *
     * @return Response
     */
    public function report(
        EntityManager $em,
        Request $request,
        Forum $forum,
        Submission $submission,
        ReportRepository $rr
    ) {
        $this->validateCsrf('report_submission', $request->request->get('token'));

        $reportBody = trim($request->request->get('reportBody'));
        $success = false;

        if(!empty($reportBody)) {
            $submission->incrementReportCount();
            $em->persist($submission);

            // Find a report for this submission. If it doesn't exist, create it.
            $report = $rr->findOneBySubmission($submission);
            if(!$report) {
                $report = new Report();
                $report->setSubmission($submission);
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
     * Get the entries for a given submission.
     *
     * @IsGranted("moderator", subject="forum")
     *
     * @param EntityManager $em
     * @param Submission    $submission
     * @param Forum         $forum
     * @param Request       $request
     *
     * @return Response
     */
    public function reportEntries(
        EntityManager $em,
        Submission $submission,
        Forum $forum,
        Request $request,
        ReportRepository $rr
    ) {
        $result = [];
        $report = $rr->findOneBySubmission($submission);

        if($report) {
            foreach($report->getEntries() as $entry) {
                $result[] = array("body" => $entry->getBody());
            }
        }

        return $this->JSONResponse($result);
    }

    /**
     * Process a report action for a given submission.
     *
     * @IsGranted("moderator", subject="forum")
     *
     * @param EntityManager $em
     * @param Submission    $submission
     * @param Forum         $forum
     * @param Request       $request
     *
     * @return Response
     */
    public function reportAction(
        EntityManager $em,
        Submission $submission,
        Forum $forum,
        Request $request,
        ReportRepository $rr
    ) {
        $action = $request->request->get('reportAction');
        $report = $rr->findOneBySubmission($submission);
        $success = false;

        if($report) {
            // Removal action.
            if($action == "remove") {
                foreach($report->getEntries() as $entry) { $em->remove($entry); }
                $em->remove($report);
                $em->refresh($submission);
                $em->remove($submission);

                $forum->addLogEntry(new ForumLogSubmissionDeletion(
                    $submission,
                    $this->getUser(),
                    "Deleted via moderation action"
                ));

                $success = true;
                $em->flush();
            }

            // Approval action.
            if($action == "approve") {
                $report->setIsResolved(true);
                $em->persist($report);

                $submission->setReportCount(0);
                $em->persist($submission);

                $success = true;
                $em->flush();
            }
        }

        return $this->JSONResponse(array("success" => $success));
    }

    protected function rerouteAwayFromAdmin() {
        if (is_null($this->getUser())) {
            return $this->redirectToRoute('login');
        }
        return $this->redirectToRoute('front');
    }
    protected function hasRightsToViewForum($forum) {
        $admin = PermissionsChecker::isAdmin($this->getUser());
        if ($admin || $forum->getId() > 0) {
            return true;
        }
        return false;
    }
}
