<?php
/**
 * ---- edit_enemy.php ----
 * Changes enemy details for selected custom enemy
 * enemy is selected from database by enemy_id
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/edit_enemy', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {

        if (isset($_SESSION['create_enemy_error'])) {
            $create_enemy_error = $_SESSION['create_enemy_error'];
            unset($_SESSION['create_enemy_error']);
        } else $create_enemy_error = "";

        $current_enemy_id = $_SESSION['current_enemy_id'];
        $current_user = $_SESSION['current_user'];
        $enemy_details = getEnemyDetails($app, $current_enemy_id, $current_user);

        return $this->view->render($response,
            'edit_enemy.html.twig',
            [
                'css_path' => CSS_PATH,
                'main_heading' => "DM Friend",
                'heading_1' => 'D&D Encounter Builder',
                'method' => 'POST',
                'action' => 'edit_enemy',
                'enemy' => $enemy_details,
                'create_enemy_error' => $create_enemy_error
            ]);
    }
})->setName('edit_enemy');


$app->post('/edit_enemy', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        $current_user = $_SESSION['current_user'];
        $current_enemy_id = $_SESSION['current_enemy_id'];

        // Clean parameters from input form (these may remain the same.
        $cleaned_parameters = cleanupEnemyData($app);
        $new_name = strtolower($cleaned_parameters['enemy_name']);
        $current_name = getEnemyDetails($app, $current_enemy_id, $current_user)['enemy_name'];

        var_dump($current_name);
        var_dump($new_name);

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
            $enemy = checkEnemyExists($app, $cleaned_parameters, $current_user);
        }
        else $enemy = false;

//         if an enemy with that name does exist, throw an error, otherwise update enemy details.
        if ($enemy) {
            // Throw error that enemy already exists
            $_SESSION['create_enemy_error'] = "That enemy already exists.";
            return $response->withRedirect($this->router->pathFor("edit_enemy"));
        } else {
            // insert enemy data into database and continue to add actions to enemy
            updateEnemyDetails($app, $current_enemy_id, $cleaned_parameters, $current_user);
            return $response->withRedirect($this->router->pathFor("enemy_overview"));
        }
    }
})->setName('edit_enemy');


/**
 * Updates selected enemy details to those input in cleaned_parameters
 * @param $app
 * @param $current_enemy_id int id of enemy to update
 * @param $cleaned_parameters array new details of enemy
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function updateEnemyDetails($app, $current_enemy_id, $cleaned_parameters, $current_user)
{
    $update_enemy['error'] = true;

    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryUpdateEnemy($database_connection, $queryBuilder, $current_enemy_id, $cleaned_parameters, $current_user);

    if($result) {
        $update_enemy['error'] = false;
    }
    return $update_enemy;
}