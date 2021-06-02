<?php

/**
 * ---- error_page.php ----
 * Displayed when the system cannot find the correct page
 * Also reached if an attempt to load a page with no $_SESSION['current_user']
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/error', function(Request $request, Response $response)
{
    session_destroy();

    return $this->view->render($response,
        'error.html.twig',
        [
            'css_path' => CSS_PATH,
            'main_heading' => "DM Friend",
            'heading_1' => 'D&D Encounter Builder',
        ]);

})->setName('error_page');

