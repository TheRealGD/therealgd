<?php

namespace App\Entity\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class BannedFromForumException extends \DomainException implements HttpExceptionInterface {
    public function __construct() {
        parent::__construct('User is banned from forum');
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode() {
        return 403;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders() {
        return [];
    }
}
