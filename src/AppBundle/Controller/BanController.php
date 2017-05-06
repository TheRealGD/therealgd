<?php

namespace Raddit\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

final class BanController extends Controller {
    /**
     * What banned users see.
     *
     * @return Response
     */
    public function landingPageAction() {
        return $this->render('@RadditApp/banned.html.twig');
    }
}
