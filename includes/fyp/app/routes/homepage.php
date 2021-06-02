<?php

/**
 * ---- homepage.php ----
 * Default page reached after signing in
 * Displays recent custom enemies and encounters created by the user
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/homepage', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        $current_user = $_SESSION['current_user'];
        $enemies = getRecentEnemies($app, $current_user);
        $encounters = getRecentEncounters($app, $current_user);

        return $this->view->render($response,
            'homepage.html.twig',
            [
                'css_path' => CSS_PATH,
                'title' => 'DM Friend - Home',
                'main_heading' => "DM Friend",
                'heading_1' => 'D&D Encounter Builder',
                'page_title' => 'Homepage',
                'current_user' => $_SESSION['current_user'],
                'enemies' => $enemies,
                'encounters' => $encounters
            ]);

    }

})->setName('homepage');

/**
 * Gets information of last two enemies created by the user
 * @param $app
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getRecentEnemies($app, $current_user) {

    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetRecentEnemies($database_connection, $queryBuilder, $current_user);

    return $result;
}

/**
 * Gets information of last two encounters created by the user
 * @param $app
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getRecentEncounters($app, $current_user) {

    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetRecentEncounters($database_connection, $queryBuilder, $current_user);

    return $result;
}