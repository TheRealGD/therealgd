<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
}
