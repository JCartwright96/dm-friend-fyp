<?php

/**
 * ---- enemy_overview.php ----
 * displays selected user enemies, showing enemy details and actions
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->get('/enemy_overview', function(Request $request, Response $response) use ($app)
{

    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {

        if(isset($_SESSION['default_enemy']) && $_SESSION['default_enemy'])
        {
            $current_enemy_id = $_SESSION['current_enemy_id'];
            $enemy_details = getEnemyDetails($app, $current_enemy_id, "default_enemy");
            $enemy_actions = getCurrentEnemyActions($app, "default_enemy", $current_enemy_id);

            return $this->view->render($response,
                'enemy_overview.html.twig',
                [
                    'css_path' => CSS_PATH,
                    'main_heading' => "DM Friend",
                    'heading_1' => 'D&D Encounter Builder',
                    'page_title' => 'Enemy Overview',
                    'default_enemy_details' => $enemy_details,
                    'default_enemy_actions' => $enemy_actions
                ]);
        }
        else{

            $current_user = $_SESSION['current_user'];
            $current_enemy_id = $_SESSION['current_enemy_id'];

            $enemy_details = getEnemyDetails($app, $current_enemy_id, $current_user);
            $enemy_actions = getCurrentEnemyActions($app, $current_user, $current_enemy_id);

            return $this->view->render($response,
                'enemy_overview.html.twig',
                [
                    'css_path' => CSS_PATH,
                    'main_heading' => "DM Friend",
                    'heading_1' => 'D&D Encounter Builder',
                    'page_title' => 'Enemy Overview',
                    'enemy_details' => $enemy_details,
                    'enemy_actions' => $enemy_actions
                ]);
        }
    }
})->setName('enemy_overview');

/**
 * This route is reached when viewing an enemy either through the
 */
$app->post('/enemy_overview', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {

        if(isset($_POST['default_enemy_name']))
        {

            $enemy_name = $_POST['default_enemy_name'];
            $current_enemy_id = getEnemyId($app, "default_enemy", $enemy_name);
            $_SESSION['current_enemy_id'] = $current_enemy_id;
            $_SESSION['default_enemy'] = true;
            return $response->withRedirect($this->router->pathFor("enemy_overview"));
        }
        else{
            unset($_SESSION['default_enemy']);
            $current_user = $_SESSION['current_user'];
            $enemy_name = $_POST['enemy_name'];
            $current_enemy_id = getEnemyId($app, $current_user, $enemy_name);
            $_SESSION['current_enemy_id'] = $current_enemy_id;
            return $response->withRedirect($this->router->pathFor("enemy_overview"));
        }
    }
})->setName('enemy_overview');

/**
 * Gets details of enemy being viewed belonging to the current user
 * @param $app
 * @param $current_enemy_id int id of enemy being viewed
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getEnemyDetails($app, $current_enemy_id, $current_user) {
//    $validated_user = ['error' => true];

    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetEnemyDetailsId($database_connection, $queryBuilder, $current_enemy_id);

    return $result;
}