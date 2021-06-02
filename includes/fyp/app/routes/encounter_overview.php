<?php

/**
 * ---- encounter_overview.php ----
 * displays selected user encounter, showing encounter details and all enemies who make an appearance
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->get('/encounter_overview', function(Request $request, Response $response) use ($app)
{

    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {

        $current_user = $_SESSION['current_user'];

        $current_encounter_id = $_SESSION['current_encounter_id'];

        $encounter_details = getEncounterDetails($app, $current_encounter_id, $current_user);

        $enemy_ids = getEnemiesInEncounter($app, $current_encounter_id, $current_user);

        // get the details of each enemy
        $enemy_details = [];

        if(isset($enemy_ids))
        {
            foreach($enemy_ids as $enemy)
            {
                $id = $enemy['enemy_id'];
                $quantity = $enemy['enemy_quantity'];

                $details = getEnemyDetails($app, $id, $current_user);

                if($details['username'] == "default_enemy")
                {
                    $details['default_enemy'] = true;
                }

                $details['quantity'] = $quantity;
                array_push($enemy_details, $details);
            }
        }

        // checks if redirect is to view_encounter or use_encounter
        if(isset($_SESSION['use_encounter']))
        {
            return $this->view->render($response,
                'use_encounter.html.twig',
                [
                    'css_path' => CSS_PATH,
                    'main_heading' => "DM Friend",
                    'heading_1' => 'D&D Encounter Builder',
                    'encounter_details' => $encounter_details,
                    'enemy_details' => $enemy_details
                ]);
        }
        else
        {
            return $this->view->render($response,
                'encounter_overview.html.twig',
                [
                    'css_path' => CSS_PATH,
                    'main_heading' => "DM Friend",
                    'heading_1' => 'D&D Encounter Builder',
                    'encounter_details' => $encounter_details,
                    'enemy_details' => $enemy_details
                ]);
        }
    }
})->setName('encounter_overview');

$app->post('/encounter_overview', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        // post to encounter_overview will take encounter name from $_POST and add it as a session variable.
        unset($_SESSION['use_encounter']);
        $current_user = $_SESSION['current_user'];
        $encounter_name = $_POST['encounter_name'];
        $_SESSION['current_encounter_id'] = getEncounterId($app, $current_user, $encounter_name);

        return $response->withRedirect($this->router->pathFor("encounter_overview"));
    }
})->setName('encounter_overview');

/**
 *---- use_encounter ----
 * This route is different from encounter_overview as it generates a more dynamic screen,
 * separating enemies into individual instances with editable stats rather than basic details and quantity
 */
$app->post('/use_encounter', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {

        // post to encounter_overview will take encounter name from $_POST and add it as a session variable.
        $current_user = $_SESSION['current_user'];
        $encounter_name = $_POST['encounter_name'];
        $_SESSION['current_encounter_id'] = getEncounterId($app, $current_user, $encounter_name);
        $_SESSION['use_encounter'] = true;

        return $response->withRedirect($this->router->pathFor("encounter_overview"));
    }
})->setName('use_encounter');

/**
 * Retrieves basic details of encounter from database
 * @param $app
 * @param $current_encounter_id int id of the encounter being viewed
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getEncounterDetails($app, $current_encounter_id, $current_user)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryFetchEncounterDetails($database_connection, $queryBuilder, $current_encounter_id, $current_user);

    return $result;
}

/**
 * Gets the id of each enemy who makes an appearance in the selected encounter
 * @param $app
 * @param $current_encounter_id int id of encounter being used
 * @param $current_user string current username
 * @return mixed array or false depending of if enemies were found
 * @throws \Doctrine\DBAL\DBALException
 */
function getEnemiesInEncounter($app, $current_encounter_id, $current_user)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetEnemiesInEncounter($database_connection, $queryBuilder, $current_encounter_id, $current_user);

    return $result;
}

