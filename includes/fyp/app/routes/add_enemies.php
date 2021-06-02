<?php
/**
 * ---- add_enemies.php ----
 * Handles adding enemies to an encounter including searching for enemies by name
 * Generates all enemy information from preset_enemies and custom enemies
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/add_enemies', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        // search bar with a potential for search that will post to same page and then do a get.
        // generate list of enemies belonging to user

        $current_user = $_SESSION['current_user'];

        if(isset($_SESSION['searched_enemy']))
        {
            $searched_enemy = $_SESSION['searched_enemy'];
        }
        else{
            $searched_enemy = false;
        }

        if($searched_enemy)
        {
            $validator = $app->getContainer()->get("validator");
            $cleaned_enemy = $validator->sanitiseString($searched_enemy);
            // generate enemies based on username and search
            $user_enemies = getSearchedEnemies($app, $current_user, $cleaned_enemy);
            $default_enemies = getSearchedEnemies($app, "default_enemy", $cleaned_enemy);
        }
        else
        {
            // generate all enemies belonging to user
            $user_enemies = getUserEnemies($app, $current_user);
            $default_enemies = getDefaultEnemies($app);
        }

        unset($_SESSION['searched_enemy']);
        $encounter_name = getEncounterDetails($app, $_SESSION['current_encounter_id'], $current_user)['encounter_name'];
        return $this->view->render($response,
            'add_enemies.html.twig',
            [
                'css_path' => CSS_PATH,
                'main_heading' => "DM Friend",
                'heading_1' => 'D&D Encounter Builder',
                'user_enemies' => $user_enemies,
                'searched_enemy' => $searched_enemy,
                'encounter_name' => $encounter_name,
                'default_enemies' => $default_enemies
            ]);
    }
})->setName('add_enemies');

$app->post('/add_enemies', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        if(isset($_POST['searched_enemy']))
        {
            var_dump($_POST['searched_enemy']);
            $_SESSION['searched_enemy'] = $_POST['searched_enemy'];
            return $response->withRedirect($this->router->pathFor("add_enemies"));
        }
        else
        {
            $current_user = $_SESSION['current_user'];
            $current_encounter_id = $_SESSION['current_encounter_id'];

            $enemy_name = $_POST['default_enemy_name'];
            $quantity = $_POST['quantity'];
            if(isset($_POST['default_enemy_name'])){
                $current_enemy_id = getEnemyId($app, "default_enemy", $enemy_name);
            }
            else
            {
                $current_enemy_id = getEnemyId($app, $current_user, $enemy_name);
            }
            $enemy_exists = checkEnemyExistsInEncounter($app, $current_encounter_id, $current_enemy_id, $current_user);


//            if(isset($_POST['default_enemy_name']))
//            {
//                $enemy_name = $_POST['default_enemy_name'];
//                $quantity = $_POST['quantity'];
//                $current_enemy_id = getEnemyId($app, "default_enemy", $enemy_name);
//                $enemy_exists = checkEnemyExistsInEncounter($app, $current_encounter_id, $current_enemy_id, $current_user);
//            }
//            else{
//                $quantity = $_POST['quantity'];
//                $enemy_name = $_POST['enemy_name'];
//                $current_enemy_id = getEnemyId($app, $current_user, $enemy_name);
//                // check if enemy exists in encounter (if it does return quantity)
//                $enemy_exists = checkEnemyExistsInEncounter($app, $current_encounter_id, $current_enemy_id, $current_user);
//            }

            if($enemy_exists)
            {
                // get current quantity from existing enemy
                $current_quantity = $enemy_exists['enemy_quantity'];
                // add existing quantity to the quantity the user wants to add
                $new_quantity = $quantity + $current_quantity;
                if($new_quantity <= 0)
                {
                    removeEnemyFromEncounter($app, $current_encounter_id, $current_enemy_id);
                    $new_quantity = 0;
                }
                // update appearances table to show new quantity of enemy for the encounter
                 updateEnemyQuantity($app, $current_encounter_id, $current_enemy_id, $new_quantity, $current_user);
            }
            else
            {
                // add enemy as long as there is atleast 1 quantity normal
                if($quantity > 0)
                {
                    addEnemyToEncounter($app, $current_encounter_id, $current_enemy_id, $quantity, $current_user);
                }
            }
            return $response->withRedirect($this->router->pathFor("encounter_overview"));
        }

    }
})->setName('add_enemies');

/**
 * Gets information from enemies based on the searched enemy name
 * @param $app
 * @param $current_user string current username
 * @param $searched_enemy string name of enemy being searched for
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getSearchedEnemies($app, $current_user, $searched_enemy)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetSearchedEnemy($database_connection, $queryBuilder, $current_user, $searched_enemy);

    return $result;
}

/**
 * Adds the selected enemy to the current encounter
 * @param $app
 * @param $current_encounter_id int id of the encounter being added to
 * @param $current_enemy_id int id of the enemy being added
 * @param $quantity int number of the selected enemy to add to the encounter
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function addEnemyToEncounter($app, $current_encounter_id, $current_enemy_id, $quantity, $current_user)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryAddEnemyToEncounter($database_connection, $queryBuilder, $current_encounter_id, $current_enemy_id, $quantity, $current_user);

    return $result;
}

/**
 * Checks an enemy with the selected name doesnt already exist in the encounter
 * @param $app
 * @param $current_encounter_id int id of the encounter being used
 * @param $current_enemy_id int id of the enemy trying to be added
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function checkEnemyExistsInEncounter($app, $current_encounter_id, $current_enemy_id, $current_user)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryCheckEnemyExistsInEncounter($database_connection, $queryBuilder, $current_encounter_id, $current_enemy_id, $current_user);
    return $result;
}

/**
 * updates the quantity of the enemy already added to the encounter
 * @param $app
 * @param $current_encounter_id int id of the current encounter
 * @param $current_enemy_id int id of the enemy being added
 * @param $new_quantity int new quantity of enemies
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function updateEnemyQuantity($app, $current_encounter_id, $current_enemy_id, $new_quantity, $current_user)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryUpdateEnemyQuantity($database_connection, $queryBuilder, $current_encounter_id, $current_enemy_id, $new_quantity, $current_user);
    return $result;
}

/**
 * Removes the enemy from the encounter, deleting from the appearances table in the database
 * @param $app
 * @param $current_encounter_id int the id of the encounter
 * @param $current_enemy_id int the id of the enemy being deleted
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function removeEnemyFromEncounter($app, $current_encounter_id, $current_enemy_id)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryRemoveEnemyFromEncounter($database_connection, $queryBuilder, $current_encounter_id, $current_enemy_id);
    return $result;
}
