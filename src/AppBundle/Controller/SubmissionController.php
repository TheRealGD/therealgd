<?php

namespace Raddit\AppBundle\Controller;

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
}
