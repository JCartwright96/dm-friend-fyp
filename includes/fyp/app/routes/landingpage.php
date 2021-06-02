<?php
/**
 * ---- landingpage.php ----
 * Page reached when first loading the website
 * Displays a login and signup form alongside welcome message
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function(Request $request, Response $response)
{
    // check permission
    session_destroy();
    unset($_SESSION['current_user']);

    $welcome_message = "Welcome to Dm Friend.";
    $welcome_message .= " Digital encounter builder to help you create and manage Dungeons and Dragons (5e) encounters!";
    $welcome_message .= " Build you encounters from a preset list of enemies, or you can create your own!";
    $login_message = "Login below, or create a FREE account if you don't have one!";

    if(isset($_SESSION['login_error'])) {
        $login_error = $_SESSION['login_error'];
        unset($_SESSION['login_error']);
    }
    else $login_error = "";

    if(isset($_SESSION['signup_error'])) {
        $signup_error = $_SESSION['signup_error'];
        unset($_SESSION['signup_error']);
    }
    else $signup_error = "";

    return $this->view->render($response,
        'landingpage.html.twig',
        [
            'css_path' => CSS_PATH,
            'title' => 'DM Friend',
            'welcome_message' => $welcome_message,
            'login_message' => $login_message,
            'login_action' => 'login',
            'signup_action' => 'signup',
            'method' => 'POST',
            'login_error' => $login_error,
            'signup_error' => $signup_error
        ]);
})->setName('landingpage');

