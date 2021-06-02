<?php

/**
 * ---- new_enemy.php ----
 * Handles creation of a new custom enemy by the user
 */
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/new_enemy', function(Request $request, Response $response)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        if (isset($_SESSION['create_enemy_error'])) {
            $create_enemy_error = $_SESSION['create_enemy_error'];
            unset($_SESSION['create_enemy_error']);
        } else $create_enemy_error = "";

        return $this->view->render($response,
            'new_enemy.html.twig',
            [
                'css_path' => CSS_PATH,
                'main_heading' => "DM Friend",
                'heading_1' => 'D&D Encounter Builder',
                'page_title' => 'Create a new Enemy',
                'method' => 'POST',
                'action' => 'new_enemy',
                'create_enemy_error' => $create_enemy_error
            ]);
    }

})->setName('new_enemy');

/**
 * After submitting the form to create enemy, redirect to post route
 * - On error creating an enemy, redirect back to the GET /new_enemy route allowing the user to try again.
 * - On success creating an enemy, continue to /add_actions
 */
$app->post('/new_enemy', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        unset($_SESSION['default_enemy']);
        // process new enemy added, then redirect with get to the add_actions page.
        $current_user = $_SESSION['current_user'];
        // Clean input parameters for a new enemy
        $cleaned_parameters = cleanupEnemyData($app);
        //var_dump($cleaned_parameters);

        if ($cleaned_parameters['error']) {
            $_SESSION['create_enemy_error'] = "Error creating enemy, please try again.";
            return $response->withRedirect($this->router->pathFor("new_enemy"));
        } else {
            $enemy = checkEnemyExists($app, $cleaned_parameters, $current_user);
        }

        if ($enemy) {
            // Throw error that enemy already exists
            $_SESSION['create_enemy_error'] = "That enemy already exists.";
            return $response->withRedirect($this->router->pathFor("new_enemy"));
        } else {
            // insert enemy data into database and continue to add actions to enemy
            addNewEnemy($app, $cleaned_parameters, $current_user);
//        $_SESSION['current_enemy_id'] = getEnemyId($app, $current_user, $cleaned_parameters['enemy_name'])['enemy_id'];
            $_SESSION['current_enemy_id'] = getEnemyId($app, $current_user, $cleaned_parameters['enemy_name']);
            return $response->withRedirect($this->router->pathFor("enemy_overview"));
        }
    }
})->setName('new_enemy');

/**
 * Validates and sanitises input information for a new enemy a user is trying to create
 * @param $app
 * @return array of cleaned parameters and an error check
 */
function cleanupEnemyData($app) {
    // takes the input data from the add_enemy_form and validates and sanitises the data
    $tainted_parameters = $_POST;
    $cleaned_parameters = [];
    $cleaned_parameters['error'] = true;

    $validator = $app->getContainer()->get("validator");

    // Clean enemy details section of form
    $cleaned_parameters['enemy_name'] = $validator->sanitiseString($tainted_parameters['enemy_name']);
    $cleaned_parameters['enemy_hp'] = $validator->sanitiseString($tainted_parameters['enemy_hp']);
    $cleaned_parameters['enemy_ac'] = $validator->sanitiseString($tainted_parameters['enemy_ac']);
    $cleaned_parameters['enemy_speed'] = $validator->sanitiseString($tainted_parameters['enemy_speed']);
//    $cleaned_parameters['extras'] = $validator->sanitiseString($tainted_parameters['extras']);

    // check if ability scores are set & validate/sanitise them
    if(isset($tainted_parameters['strength'])) {
        $cleaned_parameters['strength'] = $validator->sanitiseString($tainted_parameters['strength']);
    }
    if(isset($tainted_parameters['dexterity'])) {
        $cleaned_parameters['dexterity'] = $validator->sanitiseString($tainted_parameters['dexterity']);
    }
    if(isset($tainted_parameters['constitution'])) {
        $cleaned_parameters['constitution'] = $validator->sanitiseString($tainted_parameters['constitution']);
    }
    if(isset($tainted_parameters['intelligence'])) {
        $cleaned_parameters['intelligence'] = $validator->sanitiseString($tainted_parameters['intelligence']);
    }
    if(isset($tainted_parameters['wisdom'])) {
        $cleaned_parameters['wisdom'] = $validator->sanitiseString($tainted_parameters['wisdom']);
    }
    if(isset($tainted_parameters['charisma'])) {
        $cleaned_parameters['charisma'] = $validator->sanitiseString($tainted_parameters['charisma']);
    }

    // Checks that enemy details have passed filter and values exist for each key detail.
    if($cleaned_parameters['enemy_name'] && $cleaned_parameters['enemy_hp'] && $cleaned_parameters['enemy_ac'] && $cleaned_parameters['enemy_speed']) {
        $cleaned_parameters['error'] = false;
    }
    return $cleaned_parameters;
}

/**
 * Checks to see if an enemy with cleaned_parameters['name'] and $_SESSION['current_user'] already exists
 * @param $app
 * @param $cleaned_parameters array of details for the new enemy
 * @param $current_user string user currently logged in
 * @return mixed either false or an array with information of enemy
 * @throws \Doctrine\DBAL\DBALException
 */
function checkEnemyExists($app, $cleaned_parameters, $current_user) {
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryCheckEnemyExists($database_connection, $queryBuilder, $cleaned_parameters, $current_user);

    return $result;
}

/**
 * Adds a newly created enemy to the database under the current users username.
 * Returns an error if the query fails / enemy isn't added
 * @param $app
 * @param $cleaned_parameters array of details for the new enemy
 * @param $current_user string user currently logged in
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function addNewEnemy($app, $cleaned_parameters, $current_user) {
    $create_enemy['error'] = true;

    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryCreateNewEnemy($database_connection, $queryBuilder, $cleaned_parameters, $current_user);

    if($result) {
        $create_enemy['error'] = false;
    }
    return $create_enemy;
}

/**
 * Gets the enemy_id of the enemy using the enemy_name
 * @param $app
 * @param $current_user string current username
 * @param $current_enemy string enemy name
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getEnemyId($app, $current_user, $current_enemy) {
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetEnemyId($database_connection, $queryBuilder,$current_user, $current_enemy);

    return $result['enemy_id'];
}