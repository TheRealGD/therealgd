<?php

/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
$cdn = $container->getParameter('cdn');
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
