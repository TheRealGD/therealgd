<?php

namespace App\Controller;

use App\Entity\Votable;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class VoteController extends AbstractController {
    /**
     * Vote on a votable entity.
     *
     * @IsGranted("ROLE_USER")
     *
     * @param EntityManager $em
     * @param Request       $request
     * @param string        $entityClass
     * @param int           $id
     * @param string        $_format     'html' or 'json'
     *
     * @return Response
     */
    public function vote(EntityManager $em, Request $request, $entityClass, $id, $_format) {
        $this->validateCsrf('vote', $request->request->get('token'));

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
            return $this->redirectToRoute('front');
        }

        return $this->redirect($request->headers->get('Referer'));
    }
}
