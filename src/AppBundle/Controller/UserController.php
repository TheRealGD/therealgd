<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

final class UserController extends Controller {
    /**
     * @param User $user
     *
     * @return Response
     */
    public function userPageAction(User $user) {
        $em = $this->getDoctrine()->getManager();

        $submissions = $em->getRepository(Submission::class)->findBy(
            ['user' => $user],
            ['id' => 'DESC'],
            25
        );

        $comments = $em->getRepository(Comment::class)->findBy(
            ['user' => $user],
            ['id' => 'DESC'],
            25
        );

        return $this->render('@RadditApp/user.html.twig', [
            'submissions' => $submissions,
            'comments' => $comments,
            'user' => $user,
        ]);
    }
}
