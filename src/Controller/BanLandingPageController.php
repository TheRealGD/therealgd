<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * Landing page for banned users.
 */
final class BanLandingPageController {
    private $twig;

    public function __construct(\Twig_Environment $twig) {
        $this->twig = $twig;
    }

    public function __invoke() {
        return new Response($this->twig->render('ban/banned.html.twig'), 403);
    }
}
