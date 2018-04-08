<?php

namespace App\Repository\Submission;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class NoSubmissionsException extends \OutOfBoundsException implements HttpExceptionInterface {
    public function __construct() {
        parent::__construct('There are no submissions to display');
    }

    public function getStatusCode(): int {
        return 404;
    }

    public function getHeaders(): array {
        return [];
    }
}
