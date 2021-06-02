<?php
/**
 * ---- edit_action.php ----
 * Handles editing action information for a selected action
 */
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// This route will only be reached if there is an error when creating a new action
$app->get('/edit_action', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        if (isset($_SESSION['create_action_error'])) {
            $create_action_error = $_SESSION['create_action_error'];
            unset($_SESSION['create_action_error']);
        } else $create_action_error = "";

        // get session variables
        $current_user = $_SESSION['current_user'];
        $current_action_id =  $_SESSION['current_action_id'];

        $action_details = getActionDetails($app, $current_action_id, $current_user);

        return $this->view->render($response,
            'edit_action.html.twig',
            [
                'css_path' => CSS_PATH,
                'main_heading' => "DM Friend",
                'heading_1' => 'D&D Encounter Builder',
                'method' => 'POST',
                'action' => 'update_action',
                'action_details' => $action_details,
                'create_action_error' => $create_action_error
            ]);
    }
})->setName('edit_action');

$app->post('/edit_action', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {

        if (isset($_SESSION['create_action_error'])) {
            $create_action_error = $_SESSION['create_action_error'];
            unset($_SESSION['create_action_error']);
        } else $create_action_error = "";

        $current_user = $_SESSION['current_user'];

        // get action name from post and generate details
        $action_name = $_POST['action_name'];
        $current_action_id = getActionId($app, $current_user, $action_name);
        $_SESSION['current_action_id'] = $current_action_id;

        $action_details = getActionDetails($app, $current_action_id, $current_user);

        return $this->view->render($response,
            'edit_action.html.twig',
            [
                'css_path' => CSS_PATH,
                'main_heading' => "DM Friend",
                'heading_1' => 'D&D Encounter Builder',
                'method' => 'POST',
                'action' => 'update_action',
                'action_details' => $action_details,
                'create_action_error' => $create_action_error
            ]);
    }
})->setName('edit_action');


$app->post('/update_action', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        // Pull session variables
        $current_user = $_SESSION['current_user'];
        $current_action_id = $_SESSION['current_action_id'];
        $current_enemy_id = $_SESSION['current_enemy_id'];

        // Clean parameters from input form (these may remain the same)
        $cleaned_parameters = cleanActionParameters($app);
        $new_name = strtolower($cleaned_parameters['action_name']);
        $current_name = getActionDetails($app, $current_action_id, $current_user)['action_name'];

        var_dump($current_name);
        var_dump($new_name);

        // check if name entered is same as one currently in db
        if($new_name == strtolower($current_name))
        {
            $same_name = true;
        }
        else
            $same_name = false;

        // if names are not the same, check if the action already exists.
        if(!$same_name)
        {
            $action = checkActionExists($app, $cleaned_parameters, $current_user, $current_enemy_id);
        }
        else $action = false;

//         if an action with that name does exist, throw an error, otherwise update action details.
        if ($action) {
            // Throw error that enemy already exists
            $_SESSION['create_action_error'] = "That action already exists.";
            return $response->withRedirect($this->router->pathFor("edit_action"));
        } else {
            // insert enemy data into database and continue to add actions to enemy
            updateActionDetails($app, $current_action_id, $cleaned_parameters, $current_user);
            return $response->withRedirect($this->router->pathFor("add_actions"));
        }
    }
})->setName('update_action');


/**
 * Takes input parameters and changes action currently stored in database
 * Action is selected by action id
 * @param $app
 * @param $current_action_id int id of the action to be changed
 * @param $cleaned_parameters array details of the new action
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function updateActionDetails($app, $current_action_id, $cleaned_parameters, $current_user)
{
    $update_action['error'] = true;

    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryUpdateAction($database_connection, $queryBuilder, $current_action_id, $cleaned_parameters, $current_user);

    if($result) {
        $update_action['error'] = false;
    }
    return $update_action;
}

/**
 * Pulls information of the action by action id
 * @param $app
 * @param $current_action_id int id of the action to get details for
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getActionDetails($app, $current_action_id, $current_user)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetActionDetails($database_connection, $queryBuilder, $current_action_id, $current_user);

    return $result;
}