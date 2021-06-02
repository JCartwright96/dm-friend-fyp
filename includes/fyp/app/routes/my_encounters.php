<?php
/**
 * ---- my_encounters.php ----
 * Displays all encounters created by current user
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/my_encounters', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {

        $current_user = $_SESSION['current_user'];
        $encounters = getUserEncounters($app, $current_user);

        if(!$encounters)
        {
            $no_encounters = "You have no encounters, create some now with the button above!";
        }

        $new_encounters = [];

        foreach($encounters as $encounter)
        {
            $id = getEncounterId($app, $current_user, $encounter['encounter_name']);
            $enemies = getEnemiesInEncounter($app, $id, $current_user);
//            var_dump($enemies);
            $encounter['total_enemies'] = 0;

            foreach($enemies as $enemy)
            {
//                $encounter['total_enemies'] = $encounter['total_enemies'] +  $enemy['enemy_quantity'];
                $encounter['total_enemies'] += $enemy['enemy_quantity'];
            }

            array_push($new_encounters, $encounter);
        }

        return $this->view->render($response,
            'my_encounters.html.twig',
            [
                'css_path' => CSS_PATH,
                'main_heading' => "DM Friend",
                'heading_1' => 'D&D Encounter Builder',
                'page_title' => 'My Encounters',
                'encounter_content' => $new_encounters,
                'no_encounters' => $no_encounters

            ]);

    }
})->setName('my_encounters');


/**
 * Gets information for each encounter created by the user from the database
 * @param $app
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getUserEncounters($app, $current_user) {
    // Checks to see if an enemy with cleaned_parameters['name'] and $_SESSION['current_user'] already exists
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetUserEncounters($database_connection, $queryBuilder, $current_user);
    return $result;
}
