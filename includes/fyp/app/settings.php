<?php

$app_url = dirname($_SERVER['SERVER_NAME']);
$css_path = $app_url . '/css/fyp.css';
define('CSS_PATH', $css_path);
define('APP_URL', $app_url);

$settings = [
    "settings" => [
        'view' => [
            'template_path' => __DIR__ . '/templates/',
            'twig' => [
                'cache' => false,
                'debug' => false,
                'auto_reload' => true,
            ],
            ],
    ],
    'doctrine_settings' => [
        'driver' => 'pdo_mysql',
        'host' => '127.0.0.1',
        'dbname' => 'DM_Friend_Database',
        'port' => '3306',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],
];

return $settings;
