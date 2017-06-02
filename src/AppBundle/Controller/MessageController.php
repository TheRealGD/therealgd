<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\MessageThread;
use Raddit\AppBundle\Form\MessageReplyType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MessageController extends Controller {
    /**
     * View and reply to a message thread.
     *
     * @Security("is_granted('access', thread)")
     *
     * @param Request       $request
     * @param EntityManager $em
     * @param MessageThread $thread
     *
     * @return Response
     */
    public function messageAction(Request $request, EntityManager $em, MessageThread $thread) {
        $reply = $thread->createReply();

        $form = $this->createForm(MessageReplyType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thread->getReplies()->add($form->getData());
            $reply->setSender($this->getUser());

            $em->flush();

            return $this->redirectToRoute('raddit_app_message', [
                'id' => $thread->getId(),
            ]);
        }

        return $this->render('@RadditApp/message.html.twig', [
            'form' => $form->createView(),
            'thread' => $thread,
        ]);
    }
}
