<?php
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(
        $container['settings']['view']['template_path'],
        $container['settings']['view']['twig'],
        [
            //'cache' => 'path/to/cache',
            'debug' => true // This line should enable debug mode
        ]
    );

    // Instantiate and add Slim specific extension
    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    return $view;
};

$container['validator'] = function () {
    $validator = new \Fyp\Validator();
    return $validator;
};

$container['doctrineSqlQueries'] = function() {
    $doctrineSqlQueries = new \Fyp\DoctrineSqlQueries();
    return $doctrineSqlQueries;
};

$container['unitTesting'] = function() {
    $unitTesting = new \Fyp\UnitTesting();
    return $unitTesting;
};
