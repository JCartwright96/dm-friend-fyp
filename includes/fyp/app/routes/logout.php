<?php

/**
 * ---- logout.php ----
 * Handles logout of user
 * Destroys session and returns to landing page
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/logout', function(Request $request, Response $response) use ($app)
{
    session_destroy();
    return $response->withRedirect($this->router->pathFor("landingpage"));
})->setName('logout');

