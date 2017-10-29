<?php

namespace Raddit\AppBundle\Controller;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController {
    public function login(AuthenticationUtils $helper) {
        $lastUsername = $helper->getLastUsername();
        $error = $helper->getLastAuthenticationError();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
