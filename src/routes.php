<?php

// Home
$app->get('/', Controller\HomeController::class . ':index')->setName('home')->add($container->get('csrf'));
$app->post('/', Controller\HomeController::class . ':post')->add($container->get('csrf'));

$app->get('/download', Controller\HomeController::class . ':download')->setName('download');
$app->get('/merge', Controller\HomeController::class . ':merge')->setName('merge');