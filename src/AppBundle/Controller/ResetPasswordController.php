<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Form\RequestPasswordResetType;
use Raddit\AppBundle\Form\UserType;
use Raddit\AppBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ResetPasswordController extends Controller {
    /**
     * @param Request        $request
     * @param UserRepository $ur
     *
     * @return Response
     */
    public function requestResetAction(Request $request, UserRepository $ur) {
        if (!$this->getParameter('env(NO_REPLY_ADDRESS)')) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RequestPasswordResetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()->getEmail();

            // TODO - this is susceptible to timing attacks.
            foreach ($ur->lookUpByEmail($email) as $user) {
                $this->sendPasswordResetEmail($user, $request);
            }

            $this->addFlash('success', 'flash.reset_password_email_sent');

            return $this->redirectToRoute('raddit_app_front');
        }

        return $this->render('@RadditApp/request_password_reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request       $request
     * @param EntityManager $em
     * @param User          $user
     * @param string        $expires
     * @param string        $checksum
     *
     * @return Response
     */
    public function resetAction(Request $request, EntityManager $em, User $user, $expires, $checksum) {
        $newChecksum = $this->createChecksum($user, $expires);

        if (!hash_equals($checksum, $newChecksum)) {
            throw $this->createNotFoundException('Invalid checksum');
        }

        if (new \DateTime('@'.time()) > new \DateTime($expires)) {
            throw $this->createNotFoundException('The link has expired');
        }

        $form = $this->createForm(UserType::class, $user);

        try {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->flush();

                $this->addFlash('success', 'flash.user_password_updated');

                return $this->redirectToRoute('raddit_app_front');
            }
        } finally {
            $em->refresh($user);
        }

        return $this->render('@RadditApp/reset_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param User    $user
     * @param Request $request
     */
    private function sendPasswordResetEmail(User $user, Request $request) {
        $expires = (new \DateTime('@'.time()))->modify('+24 hours')->format('c');
        $translator = $this->get('translator');

        $siteName = $this->getParameter('env(SITE_NAME)');
        $noReplyAddress = $this->getParameter('env(NO_REPLY_ADDRESS)');

        $message = (new \Swift_Message())
            ->setFrom([$noReplyAddress => $siteName])
            ->setTo([$user->getEmail() => $user->getUsername()])
            ->setSubject($translator->trans('reset_password.email_subject', [
                '%site_name%' => $siteName,
                '%username%' => $user->getUsername(),
            ]))
            ->setBody($translator->trans('reset_password.email_body', [
                '%reset_link%' => $this->generateUrl('raddit_app_password_reset', [
                    'expires' => $expires,
                    'id' => $user->getId(),
                    'checksum' => $this->createChecksum($user, $expires),
                ], UrlGeneratorInterface::ABSOLUTE_URL),
                '%site_name%' => $siteName,
            ]));

        $message->getHeaders()->addTextHeader(
            'X-Originating-IP',
            '['.implode(', ', $request->getClientIps()).']'
        );

        $this->get('mailer')->send($message);
    }

    /**
     * @param User   $user
     * @param string $expires
     *
     * @return string
     */
    private function createChecksum(User $user, $expires) {
        $message = sprintf('%s~%s~%s',
            $user->getId(),
            $user->getPassword(),
            $expires
        );

        return hash_hmac('sha256', $message, $this->getParameter('env(SECRET)'));
    }
}
