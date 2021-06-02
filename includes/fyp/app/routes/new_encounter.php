<?php

/**
 * ---- new_encounter.php ----
 * Handles creation of a new encounter created by the user
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/new_encounter', function(Request $request, Response $response)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {

        if (isset($_SESSION['create_encounter_error'])) {
            $create_encounter_error = $_SESSION['create_encounter_error'];
            unset($_SESSION['create_encounter_error']);
        } else $create_encounter_error = "";

        return $this->view->render($response,
            'new_encounter.html.twig',
            [
                'css_path' => CSS_PATH,
                'main_heading' => "DM Friend",
                'heading_1' => 'D&D Encounter Builder',
                'page_title' => 'New Encounter',
                'method' => 'POST',
                'action' => 'new_encounter',
                'create_encounter_error' => $create_encounter_error
            ]);
    }

})->setName('new_encounter');

$app->post('/new_encounter', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        // process new enemy added, then redirect with get to the add_actions page.
        $current_user = $_SESSION['current_user'];
        // Clean input parameters for a new enemy
        $cleaned_parameters = cleanupEncounterData($app);
//        var_dump($cleaned_parameters);

        if ($cleaned_parameters['error']) {
            $_SESSION['create_encounter_error'] = "Error creating encounter, please try again.";
            return $response->withRedirect($this->router->pathFor("new_encounter"));
        } else {
            $encounter = checkEncounterExists($app, $cleaned_parameters, $current_user);
        }

        if ($encounter) {
            // Throw error that enemy already exists
            $_SESSION['create_encounter_error'] = "That encounter already exists.";
            return $response->withRedirect($this->router->pathFor("new_encounter"));
        } else {
            addNewEncounter($app, $cleaned_parameters, $current_user);
            $_SESSION['current_encounter_id'] = getEncounterId($app, $current_user, $cleaned_parameters['encounter_name']);
            return $response->withRedirect($this->router->pathFor("encounter_overview"));
        }
    }
})->setName('new_encounter');

/**
 * Cleans input data for a new encounter
 * @param $app
 * @return array
 */
function cleanupEncounterData($app) {
    // takes the input data from the add_enemy_form and validates and sanitises the data
    $tainted_parameters = $_POST;
    $cleaned_parameters = [];
    $cleaned_parameters['error'] = true;

    $validator = $app->getContainer()->get("validator");

    // Clean enemy details section of form
    $cleaned_parameters['encounter_name'] = $validator->sanitiseString($tainted_parameters['encounter_name']);
    $cleaned_parameters['encounter_location'] = $validator->sanitiseString($tainted_parameters['encounter_location']);
    $cleaned_parameters['encounter_description'] = $validator->sanitiseString($tainted_parameters['encounter_description']);
    $cleaned_parameters['encounter_notes'] = $validator->sanitiseString($tainted_parameters['encounter_notes']);

    // Checks that enemy details have passed filter and values exist for each key detail.
    if($cleaned_parameters['encounter_name'] && $cleaned_parameters['encounter_location'] && $cleaned_parameters['encounter_description'] && $cleaned_parameters['encounter_notes']) {
        $cleaned_parameters['error'] = false;
    }
    return $cleaned_parameters;
}

/**
 * Checks if an encounter by the input name already exits created by the current user
 * @param $app
 * @param $cleaned_parameters array details of encounter to be created
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function checkEncounterExists($app, $cleaned_parameters, $current_user)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryCheckEncounterExists($database_connection, $queryBuilder, $cleaned_parameters, $current_user);

    return $result;
}

/**
 * Adds a new encounter to the database belonging to the current user
 * @param $app
 * @param $cleaned_parameters array details of new encounter
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function addNewEncounter($app, $cleaned_parameters, $current_user)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryAddNewEncounter($database_connection, $queryBuilder, $cleaned_parameters, $current_user);

    return $result;
}

/**
 * Gets the encounter_id from the database by using the encounter name and current user
 * @param $app
 * @param $current_user string current username
 * @param $current_encounter string encounter name
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getEncounterId($app, $current_user, $current_encounter) {
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetEncounterId($database_connection, $queryBuilder, $current_user, $current_encounter);

    return $result['encounter_id'];
}

