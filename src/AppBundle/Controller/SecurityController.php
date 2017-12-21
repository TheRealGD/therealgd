<?php

namespace AppBundle\Controller;

use AppBundle\Mailer\ResetPasswordMailer;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController {
    public function login(AuthenticationUtils $helper, ResetPasswordMailer $mailer) {
        return $this->render('security/login.html.twig', [
            'can_reset_password' => $mailer->canMail(),
            'error' => $helper->getLastAuthenticationError(),
            'last_username' => $helper->getLastUsername(),
        ]);
    }
}
