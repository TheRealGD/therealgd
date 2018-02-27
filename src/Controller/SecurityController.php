<?php

namespace App\Controller;

use App\Mailer\ResetPasswordMailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class SecurityController extends AbstractController {
    use TargetPathTrait;

    public function login(
        AuthenticationUtils $helper,
        ResetPasswordMailer $mailer,
        Request $request
    ) {
        // store the last visited location if none exists
        if (!$this->getTargetPath($request->getSession(), 'main')) {
            $referer = $request->headers->get('Referer');

            if ($referer) {
                $this->saveTargetPath($request->getSession(), 'main', $referer);
            }
        }

        return $this->render('security/login.html.twig', [
            'can_reset_password' => $mailer->canMail(),
            'error' => $helper->getLastAuthenticationError(),
            'last_username' => $helper->getLastUsername(),
        ]);
    }
}
