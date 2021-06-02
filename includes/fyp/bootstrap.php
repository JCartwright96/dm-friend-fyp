<?php

require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/app/settings.php';

$container = new \Slim\Container($settings);

require __DIR__ . '/app/dependencies.php';

$app = new \Slim\App($container);

session_start();

require __DIR__ . '/app/routes.php';

$app->run();
