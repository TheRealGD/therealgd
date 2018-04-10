<?php

namespace App\Controller;

use App\Repository\Submission\SubmissionPager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class AbstractController extends BaseAbstractController {
    protected function submissionPage(string $sortBy, Request $request): array {
        return SubmissionPager::getParamsFromRequest($sortBy, $request);
    }

    protected function validateCsrf(string $id, string $token) {
        if (!$this->isCsrfTokenValid($id, $token)) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }
    }
}
