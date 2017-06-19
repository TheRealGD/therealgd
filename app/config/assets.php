<?php

/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
$cdn = $container->getParameter('env(CDN)');
$cdn = rtrim($cdn, '/');

if (strpos($cdn, '//') !== false) {
    $key = 'base_url';
} else {
    $key = 'base_path';
}

$container->loadFromExtension('framework', [
    'assets' => [
        $key => $cdn,
    ]
]);
