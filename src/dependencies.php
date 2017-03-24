<?php

$container = $app->getContainer();

// Logger : Monolog
$container['logger'] = function (\Slim\Container $c) {
    $config = $c->get('settings')['logger'] ;
    $logger = new \Monolog\Logger($config['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());   // Créée un id unique pour l'instance du Logger
    $fileHandler = new \Monolog\Handler\StreamHandler($config['path'], $config['level']);
    $logger->pushHandler($fileHandler);
    return $logger;
};

// Vues
$container['renderer'] = function(\Slim\Container $c) {
    $config = $c->get('settings')['renderer'] ;
    $renderer = new \Slim\Views\PhpRenderer($config['templatePath']);
    return $renderer;
};

// Csrf
$container['csrf'] = function (\Slim\Container $c) {
    $guard = new \Slim\Csrf\Guard();
    // callback en cas d'erreur
    $guard->setFailureCallable(function ($request, $response, $next) {
        // Cette callback met l'attribut csrfStatus � false
        $request = $request->withAttribute("csrfStatus", false);
        return $next($request, $response);
    });
    return $guard;
};

$container[\Controller\HomeController::class] = function(\Slim\Container $c) {
    return new \Controller\HomeController($c['logger'], $c['renderer'], $c['csrf'], $c['settings']);
};
