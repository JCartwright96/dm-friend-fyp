<?php

/**
 * ---- signup.php ----
 * Handles creation of a new user account if the user signs up to the site
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/signup', function(Request $request, Response $response) use ($app)
{
    // Clean input parameters from signup_form on landingpage.html.twig
    $cleaned_parameters = cleanSignUpParameters($app);

    // Check no errors occurred while cleaning parameters
    if($cleaned_parameters['error']) {
        $_SESSION['signup_error'] = "Failed creating account, invalid details.";
        return $response->withRedirect($this->router->pathFor("landingpage"));
    }
    // if no errors perform check password == confirm password
    else {
        $password_match = $cleaned_parameters['password'] == $cleaned_parameters['confirm_password'];
    }

    // if passwords dont match throw an error, otherwise check if user exists (less processing on an error)
    if(!$password_match) {
        $_SESSION['signup_error'] = "Failed creating account, passwords do not match.";
        return $response->withRedirect($this->router->pathFor("landingpage"));
    }
    // Check input username and email dont already exist
    else {
        $cleaned_parameters['hashed_password'] = password_hash($cleaned_parameters['password'], PASSWORD_BCRYPT);
        $new_user = checkUserAlreadyExists($app, $cleaned_parameters);
    }

    // If username + email is unique, add details to database
    if($new_user['user_exists_error']) {
        $_SESSION['signup_error'] = "Failed creating account, details may already exist";
        return $response->withRedirect($this->router->pathFor("landingpage"));
    }
    else {
        $create_user = createNewUser($app, $cleaned_parameters);
    }

    // Check to see if user details entered in database with no error before redirecting
    if($create_user['error']) {
        $_SESSION['signup_error'] = "Error creating account, please try again.";
        return $response->withRedirect($this->router->pathFor("landingpage"));
    }
    else {
        $_SESSION['current_user'] = $cleaned_parameters['username'];
        return $response->withRedirect($this->router->pathFor("homepage"));
    }

})->setName('signup');

/**
 * Cleans input user details before use
 * @param $app
 * @return mixed
 */
function cleanSignUpParameters($app) {
    $tainted_parameters = $_POST;
    $cleaned_parameters['error'] = true;

    $validator = $app->getContainer()->get("validator");

    $cleaned_parameters['username'] = $validator->sanitiseString($tainted_parameters['username_input']);
    $cleaned_parameters['email'] = $validator->validateEmail($tainted_parameters['email_input']);
    $cleaned_parameters['password'] = $validator->sanitiseString($tainted_parameters['password_input']);
    $cleaned_parameters['confirm_password'] = $validator->sanitiseString($tainted_parameters['confirm_password_input']);

    // Checks to see if both validations were successful and a value has been passed the checks
    if($cleaned_parameters['username'] && $cleaned_parameters['email'] && $cleaned_parameters['password']) {
        $cleaned_parameters['error'] = false;
    }

    return $cleaned_parameters;
}

/**
 * Checks if a user already exists by the username or email of input data
 * @param $app
 * @param $cleaned_parameters array of new user details
 * @return bool[]
 * @throws \Doctrine\DBAL\DBALException
 */
function checkUserAlreadyExists($app, $cleaned_parameters) {
    $validated_user = ['user_exists_error' => true];

    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryCheckUserExists($database_connection, $queryBuilder, $cleaned_parameters);

    if(!$result) {
        $validated_user['user_exists_error'] = false;
    }

    return $validated_user;
}

/**
 * Creates new user with input details
 * @param $app
 * @param $cleaned_parameters array user details to create new account
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function createNewUser($app, $cleaned_parameters) {
    $create_user['error'] = true;

    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryCreateNewUser($database_connection, $queryBuilder, $cleaned_parameters);

    if($result) {
        $create_user['error'] = false;
    }

    return $create_user;

}
