<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\WikiPage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class WikiController extends Controller {
    public function wikiAction(WikiPage $page = null) {
        if (!$page) {
            // todo
            throw $this->createNotFoundException('No such page');
        }

        return $this->render('@RadditApp/wiki.html.twig', [
            'page' => $page,
        ]);
    }
}
