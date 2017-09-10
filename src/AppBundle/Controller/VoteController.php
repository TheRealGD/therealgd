<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\Votable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class VoteController extends Controller {
    /**
     * Vote on a votable entity.
     *
     * @Security("is_granted('ROLE_USER')")
     *
     * @param EntityManager $em
     * @param Request       $request
     * @param string        $entityClass
     * @param int           $id
     * @param string        $_format     'html' or 'json'
     *
     * @return Response
     */
    public function voteAction(EntityManager $em, Request $request, $entityClass, $id, $_format) {
        if (!$this->isCsrfTokenValid('vote', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Bad CSRF token');
        }

        $choice = $request->request->getInt('choice', null);

        if (!in_array($choice, Votable::VOTE_CHOICES, true)) {
            throw new BadRequestHttpException('Bad choice');
        }

        $entity = $em->find($entityClass, $id);

        if (!$entity instanceof Votable) {
            throw $this->createNotFoundException('Entity not found');
        }

        $entity->vote($this->getUser(), $request->getClientIp(), $choice);

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
