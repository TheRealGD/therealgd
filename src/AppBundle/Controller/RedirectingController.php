<?php

namespace Raddit\AppBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Seized from <https://symfony.com/doc/current/routing/redirect_trailing_slash.html>
 */
class RedirectingController {
    public function removeTrailingSlashAction(Request $request) {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return new RedirectResponse($url, 301);
    }
}
