<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractController extends BaseAbstractController {
    protected $logger = null;
    public function __construct(LoggerInterface $logger = null) {
        $this->logger = $logger ?: new NullLoger();
    }

    protected function validateCsrf(string $id, string $token) {
        if (!$this->isCsrfTokenValid($id, $token)) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }
    }

    /**
     * Create a json encoded response.
     */
    protected function JSONResponse($responseObject): Response {
        $response = new Response(json_encode($responseObject));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
