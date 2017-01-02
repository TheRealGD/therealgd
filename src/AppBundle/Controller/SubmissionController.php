<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Repository\SubmissionRepository;
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
final class SubmissionController extends Controller {
    /**
     * View submissions on the front page.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function frontPageAction(Request $request) {
        $sortBy = $this->getSortBy($request);

        $submissions = $this->getDoctrine()->getRepository(Submission::class)
            ->findSortedQb($sortBy)
            ->setMaxResults(20)
            ->getQuery()
            ->execute();

        return $this->render('@RadditApp/front.html.twig', [
            'sort_by' => $sortBy,
            'submissions' => $submissions,
        ]);
    }

    /**
     * Show the front page of a given forum.
     *
     * @param Forum   $forum
     * @param Request $request
     *
     * @return Response
     */
    public function forumAction(Forum $forum, Request $request) {
        $sortBy = $this->getSortBy($request);

        $submissions = $this->getDoctrine()->getRepository(Submission::class)
            ->findSortedQb($sortBy)
            ->andWhere('s.forum = :forum')
            ->setParameter('forum', $forum)
            ->getQuery()
            ->execute();

        return $this->render('@RadditApp/forum.html.twig', [
            'forum' => $forum,
            'sort_by' => $sortBy,
            'submissions' => $submissions,
        ]);
    }

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

    /**
     * @param Request $request
     *
     * @return mixed|string
     */
    private function getSortBy(Request $request) {
        $sortBy = $request->query->get('sort');

        if (!in_array($sortBy, SubmissionRepository::SORT_TYPES)) {
            return 'hot';
        }

        return $sortBy;
    }
}
