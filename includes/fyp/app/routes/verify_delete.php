<?php
/**
 * ---- verify_delete.php ----
 * Handles all of the delete verification related to:
 *  - Delete account
 *  - Delete enemy
 *  - Delete action
 *  - Delete encounter
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/delete_enemy', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        $current_user = $_SESSION['current_user'];
        $enemy_to_delete = getEnemyId($app, $current_user, $_POST['enemy_name']);

        deleteEnemy($app, $enemy_to_delete, $current_user);
        return $response->withRedirect($this->router->pathFor("my_enemies"));
    }
})->setName('delete_enemy');


$app->post('/delete_encounter', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        $current_user = $_SESSION['current_user'];
        $encounter_to_delete = getEncounterId($app, $current_user, $_POST['encounter_name']);

        deleteEncounter($app, $encounter_to_delete, $current_user);

        return $response->withRedirect($this->router->pathFor("my_encounters"));
    }
})->setName('delete_encounter');


$app->post('/delete_action', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        $current_user = $_SESSION['current_user'];
        $action_to_delete = getActionId($app, $current_user, $_POST['action_name']);

        deleteAction($app, $action_to_delete, $current_user);
        return $response->withRedirect($this->router->pathFor("add_actions"));
    }
})->setName('delete_action');

$app->get('/delete_account', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        if(isset($_SESSION['delete_error'])) {
            $delete_error = $_SESSION['delete_error'];
            unset($_SESSION['delete_error']);
        }
        else $delete_error = "";

        return $this->view->render($response,
            'delete_account.html.twig',
            [
                'css_path' => CSS_PATH,
                'main_heading' => "DM Friend",
                'heading_1' => 'D&D Encounter Builder',
                'page_title' => 'My Enemies',
                'action' => 'delete_account',
                'method' => 'POST',
                'delete_error' => $delete_error

            ]);
    }
})->setName('delete_account');

$app->post('/delete_account', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {

        $current_user = $_SESSION['current_user'];

        $user_details['username'] = $current_user;
        $user_details['password'] = $_POST['password'];

        $password_match = validateUserExists($app, $user_details);

        if($password_match['error'])
        {
            $_SESSION['delete_error'] = true;
            return $response->withRedirect($this->router->pathFor("delete_account"));
        }
        else{
            deleteAccount($app, $current_user);
            return $response->withRedirect($this->router->pathFor("landingpage"));
        }
    }
})->setName('delete_account');

/**
 * Deletes the account of the current user, removing all enemies, encounters and actions created by them
 * @param $app
 * @param $current_user
 * @throws \Doctrine\DBAL\DBALException
 */
function deleteAccount($app,$current_user)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');

    $doctrine_queries::queryDeleteUser($database_connection, $queryBuilder, $current_user);

}

/**
 * Deletes the enemy selected by the user
 * Deletes all actions associated with that enemy as well
 * Can only delete custom enemies
 * @param $app
 * @param $enemy_to_delete int id of the enemy to delete
 * @param $current_user string current user
 * @throws \Doctrine\DBAL\DBALException
 */
function deleteEnemy($app, $enemy_to_delete, $current_user)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');

    // Delete Enemy Actions
    $doctrine_queries::queryDeleteEnemyActions($database_connection, $queryBuilder, $enemy_to_delete, $current_user);

    // Delete Enemy Appearances
    $doctrine_queries::queryDeleteEnemyAppearances($database_connection, $queryBuilder, $enemy_to_delete, $current_user);

    // Delete Enemy from enemy table
    $doctrine_queries::queryDeleteEnemy($database_connection, $queryBuilder, $enemy_to_delete, $current_user);

}

/**
 * Deletes the encounter selected by the user
 * @param $app
 * @param $encounter_to_delete int id of the encounter to delete
 * @param $current_user string current username
 * @throws \Doctrine\DBAL\DBALException
 */
function deleteEncounter($app, $encounter_to_delete, $current_user)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');

    // Delete Encounter appearances
    $doctrine_queries::queryDeleteEncounterAppearances($database_connection, $queryBuilder, $encounter_to_delete, $current_user);

    // Delete Encounter from encounter table
    $doctrine_queries::queryDeleteEncounter($database_connection, $queryBuilder, $encounter_to_delete, $current_user);

}

/**
 * Deletes the action selected by the user associated with a current enemy
 * @param $app
 * @param $action_to_delete int id of the action to delete
 * @param $current_user string current username
 * @throws \Doctrine\DBAL\DBALException
 */
function deleteAction($app, $action_to_delete, $current_user)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');

    // Delete Encounter appearances
    $doctrine_queries::queryDeleteAction($database_connection, $queryBuilder, $action_to_delete, $current_user);

}
