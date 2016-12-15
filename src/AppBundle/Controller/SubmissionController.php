<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
}
