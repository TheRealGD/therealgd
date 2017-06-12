<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\WikiPage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

final class WikiController extends Controller {
    public function wikiAction(WikiPage $page = null) {
        if (!$page) {
            $response = new Response('', 404);

            return $this->render('@RadditApp/wiki_404.html.twig', [], $response);
        }

        return $this->render('@RadditApp/wiki.html.twig', [
            'page' => $page,
        ]);
    }
}
