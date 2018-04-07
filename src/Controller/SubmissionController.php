<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Forum;
use App\Entity\ForumLogSubmissionDeletion;
use App\Entity\ForumLogSubmissionLock;
use App\Entity\Submission;
use App\Form\DeleteReasonType;
use App\Form\Model\SubmissionData;
use App\Form\SubmissionType;
use App\Repository\SubmissionRepository;
use App\Repository\ForumRepository;
use App\Repository\UserRepository;
use App\Utils\Slugger;
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
    public function submit(EntityManager $em, Request $request, Forum $forum = null) {
        $data = new SubmissionData($forum);

        $form = $this->createForm(SubmissionType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submission = $data->toSubmission($this->getUser(), $request->getClientIp());

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

    public function importFromReddit(EntityManager $em, Request $request, SubmissionRepository $sr, UserRepository $ur, ForumRepository $fr, Translator $tr = null) {
        // LOCAL ONLY BRUH
        if ($request->getClientIp() != "127.0.0.1") return;

        // Prime Response
        $successReport = [
          "success" => 'false',
          "imported" => array(),
          "ignored" => array()
        ];

        // Check top 10 every time.
        $redditUrl = "https://www.reddit.com/r/gundeals/new.json?sort=new&limit=10";

        try {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $redditUrl);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json')); // Assuming you're requesting JSON
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

          $response = curl_exec($ch);

          // The goods.
          $data = json_decode($response);
          foreach ($data->data->children as $posting)
          {
            $url = $posting->data->url;
            $title = $posting->data->title;
            $permalink = $posting->data->permalink;
            $hidden = $posting->data->hidden;
            $pinned = $posting->data->pinned;
            $modbanned = $posting->data->banned_by;

            // Only import non banned and non hidden and non pinned posts
            if ($hidden == false && $modbanned == null && $pinned == false){
              // We got that url? YAAA BOY!
              if ($url != null && $title != null)
              {

                /* Check for submission with url of incoming url */
                $foundEntry = $sr->findOneByUrl($url);
                if ($foundEntry == null){
                  /* If not, create submission */
                  $forum = $fr->findOneByCaseInsensitiveName("gundeals");

                  $data = new SubmissionData($forum);
                    $data->setTitle($title);
                    $data->setUrl($url);
                    $data->setBody("Imported From /r/gundeals. | " . $permalink);
                    $data->setSticky(false);
                    $data->setModThread(false);

                  $user = $ur->loadUserByUsername("gundeals");
                  $submission = $data->toSubmission($user, $request->getClientIp());

                  $em->persist($submission);
                  $em->flush();

                  array_push($successReport['imported'], $url);
                  $successReport['success'] = 'true';
                } else {
                  array_push($successReport['ignored'], $url);
                  $successReport['success'] = 'true';
                }
              } else {

              }
            }
          }
        } catch (Exception $e){
          // fuk.
        }

        $textResponse = new Response(json_encode($successReport), 200);
        $textResponse->headers->set('Content-Type', 'application/json');
        return $textResponse;
    }
}
