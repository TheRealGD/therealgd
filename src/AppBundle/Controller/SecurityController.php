<?php

namespace Raddit\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

final class SecurityController extends Controller {
    /**
     * Shows the login form.
     *
     * @return Response
     */
    public function loginAction() {
        $helper = $this->get('security.authentication_utils');

        $lastUsername = $helper->getLastUsername();
        $error = $helper->getLastAuthenticationError();

        return $this->render('@RadditApp/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
