<?php

/**
 * --- account_settings.php ---
 * This route manages everything involved with a users accounts
 *  - Change Password
 *  - Delete Account
 *  - View account details
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/account_settings', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        $current_user = $_SESSION['current_user'];
        $user_details = getUserDetails($app, $current_user);

        if(isset($_SESSION['error_message'])) {
            $error_message = $_SESSION['error_message'];
            unset($_SESSION['error_message']);
        }
        else $error_message = "";

        if(isset($_SESSION['success_message'])) {
            $success_message = $_SESSION['success_message'];
            unset($_SESSION['success_message']);
        }
        else $success_message = "";

        return $this->view->render($response,
            'account_settings.html.twig',
            [
                'css_path' => CSS_PATH,
                'main_heading' => "DM Friend",
                'heading_1' => 'D&D Encounter Builder',
                'page_title' => "My Account",
                'user_details' => $user_details,
                'action' => 'change_password',
                'method' => 'POST',
                'error_message' => $error_message,
                'success_message' => $success_message
            ]);
    }
})->setName('account_settings');

$app->post('/change_password', function(Request $request, Response $response) use ($app)
{
    if(!isset($_SESSION['current_user'])) {
        return $response->withRedirect($this->router->pathFor("error_page"));
    }
    else {
        $current_user = $_SESSION['current_user'];

        // clean input password data
        $cleaned_parameters = cleanPasswordParameters($app);
        $cleaned_parameters['username'] = $current_user;

        // check there is no error with returned data
        if(!$cleaned_parameters['error'])
        {
            // verify input for current password matches what is saved in database
            $verify_password = validateUserExists($app, $cleaned_parameters);
        }
        else{
            $_SESSION['error_message'] = "Error changing password";
            return $response->withRedirect($this->router->pathFor("account_settings"));
        }

        if(!$verify_password['error'])
        {
            $_SESSION['success_message'] = "Password Changed Successfully";
            changeUserPassword($app, $cleaned_parameters, $current_user);
            return $response->withRedirect($this->router->pathFor("account_settings"));
        }
        else{
            $_SESSION['error_message'] = "Error changing password";
            return $response->withRedirect($this->router->pathFor("account_settings"));
        }
    }
})->setName('change_password');


/**
 * Gets the details from the database relating to the input username
 * @param $app
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getUserDetails($app, $current_user)
{
    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryGetUserDetails($database_connection, $queryBuilder, $current_user);

    return $result;
}


/**
 * Cleans new password information before handling
 * @param $app
 * @return array
 */
function cleanPasswordParameters($app)
{
    $tainted_parameters = $_POST;
    $cleaned_parameters = [];
    $cleaned_parameters['error'] = true;

    $validator = $app->getContainer()->get("validator");

    $cleaned_parameters['password'] = $validator->sanitiseString($tainted_parameters['current_password']);
    $cleaned_parameters['new_password'] = $validator->sanitiseString($tainted_parameters['new_password']);
    $cleaned_parameters['confirm_password'] = $validator->sanitiseString($tainted_parameters['confirm_password']);

    // Check that a value is returned for each password_input after cleansing
    if($cleaned_parameters['password'] && $cleaned_parameters['new_password'] && $cleaned_parameters['confirm_password'])
    {
        $cleaned_parameters['error'] = false;
    }

    // if all values exist, check that new_password and password_confirm are equal
    if(!$cleaned_parameters['error'])
    {
        if($cleaned_parameters['new_password'] == $cleaned_parameters['confirm_password'])
        {
            $cleaned_parameters['error'] = false;
        }
        else{
            $cleaned_parameters['error'] = true;
        }
    }

    return $cleaned_parameters;
}

/**
 * Changes the hashed password for the current user
 * @param $app
 * @param $cleaned_parameters string the new password to be stored
 * @param $current_user string current username
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function changeUserPassword($app, $cleaned_parameters, $current_user)
{

    $new_password = password_hash($cleaned_parameters['new_password'], PASSWORD_BCRYPT);

    $database_settings = $app->getContainer()->get('doctrine_settings');
    $database_connection = \Doctrine\DBAL\DriverManager::getConnection($database_settings);
    $queryBuilder = $database_connection->createQueryBuilder();
    $doctrine_queries = $app->getContainer()->get('doctrineSqlQueries');
    $result = $doctrine_queries::queryChangeUserPassword($database_connection, $queryBuilder, $new_password, $current_user);

    return $result;


}