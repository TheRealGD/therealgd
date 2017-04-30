<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\Votable;
use Raddit\AppBundle\Entity\Vote;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class VoteController extends Controller {
    /**
     * Vote on a votable entity.
     *
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request $request
     * @param string  $entityClass
     * @param int     $id
     * @param string  $_format     'html' or 'json'
     *
     * @return Response
     */
    public function voteAction(Request $request, $entityClass, $id, $_format) {
        if (!$this->isCsrfTokenValid('vote', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Bad CSRF token');
        }

        $choice = $request->request->getInt('choice', null);

        if (!isset($choice) || $choice < -1 || $choice > 1) {
            throw $this->createNotFoundException('Bad choice');
        }

        $em = $this->getDoctrine()->getManager();
        $entity = $em->find($entityClass, $id);

        if (!$entity instanceof Votable) {
            throw $this->createNotFoundException('Entity not found');
        }

        /** @var Vote $vote */
        $vote = $entity->getUserVote($this->getUser());

        if ($vote) {
            switch ($choice) {
            case -1:
                $vote->setUpvote(false);
                break;
            case 0:
                $em->remove($vote);
                break;
            case 1:
                $vote->setUpvote(true);
                break;
            }
        } elseif ($choice !== 0) {
            $vote = $entity->createVote();
            $vote->setUpvote($choice === 1);
            $vote->setUser($this->getUser());

            $em->persist($vote);
        }

        $em->flush();

        if ($_format === 'json') {
            return $this->json(['message' => 'successful vote']);
        }

        if (!$request->headers->has('Referer')) {
            return $this->redirectToRoute('raddit_app_front');
        }

        return $this->redirect($request->headers->get('Referer'));
    }
}
