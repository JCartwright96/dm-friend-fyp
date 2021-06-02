<?php

/**
 * ---- default_enemies.php ----
 * Gets information related to preset enemies already loaded into the app
 */
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/default_enemies', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }

    else {
        $enemies = getDefaultEnemies($app);

        return $this->view->render($response,
            'default_enemies.html.twig',
            [
                'css_path' => CSS_PATH,
                'main_heading' => "DM Friend",
                'heading_1' => 'D&D Encounter Builder',
                'enemies' => $enemies
            ]);
    }
})->setName('default_enemies');

/**
 * Queries the database to get enemy information for all enemies with username "preset_enemy"
 * @param $app
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getDefaultEnemies($app) {
    // Checks to see if an enemy with cleaned_parameters['name'] and $_SESSION['current_user'] already exists
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetDefaultEnemies($database_connection, $queryBuilder);
    return $result;
}
