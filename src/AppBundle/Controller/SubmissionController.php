<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\Submission;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SubmissionController extends Controller {
    /**
     * View submissions on the front page.
     */
    public function frontPageAction() {
        $submissions = $this->getDoctrine()->getRepository(Submission::class)
            ->findBy([], ['id' => 'DESC'], 20);

        return $this->render('front.html.twig', [
            'submissions' => $submissions,
        ]);
    }
}
