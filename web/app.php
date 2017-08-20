<?php

use Symfony\Component\HttpFoundation\Request;

/* @noinspection PhpIncludeInspection */
@include __DIR__.'/../var/maintenance.php';

require __DIR__.'/../vendor/autoload.php';

$kernel = new AppKernel('prod', false);
//$kernel = new AppCache($kernel);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
