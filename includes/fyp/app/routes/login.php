<?php
/**
 * login.php
 *
 * Handles sanitization and validation of input user data
 * Redirects accordingly depending on if user exists or not
 */
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/login', function(Request $request, Response $response) use ($app)
{

    $validated_user = false;
    // clean input parameters
    $cleaned_parameters = cleanLoginParameters($app);

    // Check to see if parameters were cleaned with no error
    if($cleaned_parameters['error']) {
        $_SESSION['login_error'] = "Incorrect Login Details";
        return $response->withRedirect($this->router->pathFor("landingpage"));
    }
    else {
        $validated_user = validateUserExists($app, $cleaned_parameters);
    }

    // Checks if user exists, and redirects accordingly
    if(!$validated_user['error']) {
        $_SESSION['current_user'] = $validated_user['username'];
        return $response->withRedirect($this->router->pathFor("homepage"));
    }
    else {
        $_SESSION['login_error'] = "Incorrect Login Details";
        return $response->withRedirect($this->router->pathFor("landingpage"));
    }

})->setName('login');

/**
 * Takes user input form data from $_POST array
 * Cleans username and password, stores in $cleaned_parameters
 * $cleaned_parameters['error'] indicates if there were issues while sanitising data
 * @param $app
 * @return array $cleaned_parameters
 */

function cleanLoginParameters($app) {
    $tainted_parameters = $_POST;
    $cleaned_parameters = [];
    $cleaned_parameters['error'] = true;

    $validator = $app->getContainer()->get("validator");

    $cleaned_parameters['username'] = $validator->sanitiseString($tainted_parameters['username_input']);
    $cleaned_parameters['password'] = $validator->sanitiseString($tainted_parameters['password_input']);

    // Checks to see if both validations were successful and a value has been passed the checks
    if($cleaned_parameters['username'] && $cleaned_parameters['password']) {
        $cleaned_parameters['error'] = false;
    }

    return $cleaned_parameters;
}

/**
 *
 * Uses the Doctrine QueryBuilder API to store the check if username exists in db and checks password is correct.
 *
 * @param $app
 * @param array $cleaned_parameters
 * @return array $validated_user
 * @throws \Doctrine\DBAL\DBALException
 */
function validateUserExists($app, $cleaned_parameters) {
    $validated_user = ['error' => true];

    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryCheckUserExists($database_connection, $queryBuilder, $cleaned_parameters);

    if($result && password_verify($cleaned_parameters['password'], $result['password'])) {
        $validated_user['username'] = $result['username'];
        $validated_user['error'] = false;
    }
    else {
        $validated_user['error'] = true;
    }

    return $validated_user;
}
