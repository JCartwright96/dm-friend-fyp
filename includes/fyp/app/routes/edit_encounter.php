<?php

/**
 * ---- edit_encounter.php ----
 * Changes encounter details for selected user encounter
 * encounter is selected from database by encounter_id
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/edit_encounter', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        if (isset($_SESSION['create_encounter_error'])) {
            $create_encounter_error = $_SESSION['create_encounter_error'];
            unset($_SESSION['create_encounter_error']);
        } else $create_encounter_error = "";

        $current_encounter_id = $_SESSION['current_encounter_id'];
        $current_user = $_SESSION['current_user'];
        $encounter_details = getEncounterDetails($app, $current_encounter_id, $current_user);

        return $this->view->render($response,
            'edit_encounter.html.twig',
            [
                'css_path' => CSS_PATH,
                'main_heading' => "DM Friend",
                'heading_1' => 'D&D Encounter Builder',
                'method' => 'POST',
                'action' => 'edit_encounter',
                'encounter' => $encounter_details,
                'create_encounter_error' => $create_encounter_error
            ]);
    }
})->setName('edit_encounter');


$app->post('/edit_encounter', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        $current_encounter_id = $_SESSION['current_encounter_id'];
        $current_user = $_SESSION['current_user'];

        // Clean parameters from input form (these may remain the same.
        $cleaned_parameters = cleanupEncounterData($app);
        $new_name = strtolower($cleaned_parameters['encounter_name']);
        $current_name = getEncounterDetails($app, $current_encounter_id, $current_user)['encounter_name'];

        // check if name entered is same as one currently in db
        if($new_name == strtolower($current_name))
        {
            $same_name = true;
        }
        else
            $same_name = false;

        // if names are not the same, check if the enemy already exists.
        if(!$same_name)
        {
            $encounter = checkEncounterExists($app, $cleaned_parameters, $current_user);
        }
        else $encounter = false;

//         if an enemy with that name does exist, throw an error, otherwise update enemy details.
        if ($encounter) {
            // Throw error that enemy already exists
            $_SESSION['create_encounter_error'] = "That encounter already exists.";
            return $response->withRedirect($this->router->pathFor("edit_encounter"));
        } else {
            // insert enemy data into database and continue to add actions to enemy
            updateEncounterDetails($app, $current_encounter_id, $cleaned_parameters, $current_user);
            return $response->withRedirect($this->router->pathFor("encounter_overview"));
        }
    }
})->setName('edit_encounter');

/**
 * Changes details of selected encounter to those input in cleaned_parameters
 * @param $app
 * @param $current_encounter_id int encounter to change
 * @param $cleaned_parameters array new details for the encounter
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function updateEncounterDetails($app, $current_encounter_id, $cleaned_parameters, $current_user)
{
    $update_encounter['error'] = true;

    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryUpdateEncounter($database_connection, $queryBuilder, $current_encounter_id, $cleaned_parameters, $current_user);

    if($result) {
        $update_encounter['error'] = false;
    }
    return $update_encounter;
}