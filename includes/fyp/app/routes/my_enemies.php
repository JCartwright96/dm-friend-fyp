<?php

/**
 * ---- my_enemies.php ----
 * Displays all custom enemies created by current user
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/my_enemies', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {

        $current_user = $_SESSION['current_user'];
        $enemies = getUserEnemies($app, $current_user);

        if(!$enemies)
        {
            $no_enemies = "You have no custom enemies, create some now with the button above!";
        }

        $new_encounters = [];

        return $this->view->render($response,
            'my_enemies.html.twig',
            [
                'css_path' => CSS_PATH,
                'main_heading' => "DM Friend",
                'heading_1' => 'D&D Encounter Builder',
                'page_title' => 'My Enemies',
                'enemies_content' => $enemies,
                'no_enemies' => $no_enemies

            ]);

    }
})->setName('my_enemies');


/**
 * Gets all enemies belonging to current user from database
 * @param $app
 * @param $current_user string current user
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getUserEnemies($app, $current_user) {
    // Checks to see if an enemy with cleaned_parameters['name'] and $_SESSION['current_user'] already exists
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetUserEnemies($database_connection, $queryBuilder, $current_user);
    return $result;
}

