<?php
/**
 * ---- add_actions.php ----
 * This route manages adding actions to enemies
 *  - Creating a new action
 *  - Dynamically refreshing the page to display current actions
 *  - Updating actions for the enemy in the database
 */


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/add_actions', function(Request $request, Response $response) use ($app)
{

    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {

    $current_enemy_id = $_SESSION['current_enemy_id'];
    $current_user = $_SESSION['current_user'];
    $current_enemy_name = getEnemyDetails($app, $current_enemy_id, $current_user)['enemy_name'];

    $current_actions = getCurrentEnemyActions($app, $current_user, $current_enemy_id);

    // Generate a message if redirecting from the add_action page itself to confirm success
    $error_message = "";
    $success_message = "";
    // Generate error message if an action already exists with that name for that enemy
    if(isset($_SESSION['action_exists']) && $_SESSION['action_exists']) {
        $error_message = "Error - Action already exists for this enemy";
    }

    // Generate appropriate depending on whether the action is added or not
    if(isset($_SESSION['action_added']) && $_SESSION['action_added']) {
        $success_message = "Action added successfully";
    }
    if(isset($_SESSION['action_added']) && !$_SESSION['action_added']) {
        $error_message = "Error - Failed adding action, please try again.";
    }

    unset($_SESSION['action_exists']);
    unset($_SESSION['action_added']);

    return $this->view->render($response,
        'add_actions.html.twig',
        [
            'css_path' => CSS_PATH,
            'main_heading' => "DM Friend",
            'heading_1' => 'D&D Encounter Builder',
            'page_title' => 'Actions',
            'method' => 'POST',
            'action' => 'add_actions',
            'current_actions' => $current_actions,
            'error_message' => $error_message,
            'success_message' => $success_message,
            'enemy_name' => $current_enemy_name,
        ]);

    }
})->setName('add_actions');

$app->post('/add_actions', function(Request $request, Response $response) use ($app) {

    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
    // after adding a new action it will post to the same page, and add the action.
    // the final submit button will HTTP GET the enemy overview page with the current enemy data is being added to

    // grab session variables for user and enemy
    $current_enemy_id = $_SESSION['current_enemy_id'];
    $current_user = $_SESSION['current_user'];

    // clean action parameters
    $cleaned_parameters = cleanActionParameters($app);

    // check the action doesnt already exist
    $action = checkActionExists($app, $cleaned_parameters, $current_user, $current_enemy_id);
//    var_dump($action);

    if(!$action) {
        $add_action_result = addNewAction($app, $cleaned_parameters, $current_user, $current_enemy_id);
    }
    else {
        $_SESSION['action_exists'] = true;
        return $response->withRedirect($this->router->pathFor("add_actions"));
    }

    if($add_action_result) {
        $_SESSION['action_added'] = true;
        return $response->withRedirect($this->router->pathFor("add_actions"));
    }
    else {
        $_SESSION['action_added'] = true;
        return $response->withRedirect($this->router->pathFor("add_actions"));
    }

    }

})->setName('add_actions');

/**
 * Cleans input action parameters from the input form
 * Uses the post array of parameters
 * @param $app
 * @return array
 */
function cleanActionParameters($app) {
    $tainted_parameters = $_POST;
    $cleaned_parameters = [];
    $cleaned_parameters['error'] = true;

    $validator = $app->getContainer()->get("validator");

    // Clean action details
    $cleaned_parameters['action_name'] = $validator->sanitiseString($tainted_parameters['action_name']);
    $cleaned_parameters['action_reach'] = $validator->sanitiseString($tainted_parameters['action_reach']);
    $cleaned_parameters['action_area'] = $validator->sanitiseString($tainted_parameters['action_area']);
    $cleaned_parameters['action_hit'] = $validator->sanitiseString($tainted_parameters['action_hit']);
    $cleaned_parameters['action_damage'] = $validator->sanitiseString($tainted_parameters['action_damage']);
    $cleaned_parameters['action_modifier'] = $validator->sanitiseString($tainted_parameters['action_modifier']);

    // Check if all parameters were successfully cleaned and returned either a value or false
    if($cleaned_parameters['action_name'] && $cleaned_parameters['action_reach'] && $cleaned_parameters['action_hit'] && $cleaned_parameters['action_damage'] && $cleaned_parameters['action_modifier']) {
        $cleaned_parameters['error'] = false;
    }
    return $cleaned_parameters;
}

/**
 * Before a new action is created this function checks if an action belonging to the enemy_id already exists by name
 * @param $app
 * @param $cleaned_parameters array the action details
 * @param $current_user string current user name
 * @param $current_enemy_id int enemy_id for the enemy to add the action to
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function checkActionExists($app, $cleaned_parameters, $current_user, $current_enemy_id) {
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryCheckActionExists($database_connection, $queryBuilder, $cleaned_parameters, $current_user, $current_enemy_id);

    return $result;
}

/**
 * Adds the action to the database referencing the enemy by enemy_id
 * @param $app
 * @param $cleaned_parameters string the action details
 * @param $current_user string current username
 * @param $current_enemy_id int current enemy_id to add the action to
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function addNewAction($app, $cleaned_parameters, $current_user, $current_enemy_id) {
    $add_action['error'] = true;

    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryAddNewAction($database_connection, $queryBuilder, $cleaned_parameters, $current_user, $current_enemy_id);

    if($result) {
        $add_action['error'] = false;
    }
    return $add_action;
}

/** Gets all actions from the database by enemy_id
 *  Returns them for display to show current actions belonging to the enemy
 * @param $app
 * @param $current_user string the current username
 * @param $current_enemy_id int the enemy_id to get actions for
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getCurrentEnemyActions($app, $current_user, $current_enemy_id) {

    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetCurrentEnemyActions($database_connection, $queryBuilder, $current_user, $current_enemy_id);

    return $result;

}

/**
 * Returns the action_id by searching for action_name and username in the database
 * @param $app
 * @param $current_user string the current username
 * @param $action_name string the action name
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getActionId($app, $current_user, $action_name) {
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetActionId($database_connection, $queryBuilder,$current_user, $action_name);

    return $result['action_id'];
}