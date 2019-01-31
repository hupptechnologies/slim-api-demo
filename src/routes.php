<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\Middleware\AuthMiddleware;

// Routes
$app->post('/login','AuthController:login');
$app->post('/signup','AuthController:signup');


// Auth route group
$app->group('/user', function () {

	$this->get('/logout','AuthController:logout');
	$this->post('/update_profile','UserUpdateController:update_profile');
	$this->post('/update_profile_photo','UserUpdateController:update_profile_photo');
	$this->post('/push_token','UserUpdateController:push_token');

})->add(new AuthMiddleware($container));

$app->post('/password_reset_intial_request','AuthController:password_reset_intial_request');
$app->post('/password_reset_token_validation','AuthController:password_reset_token_validation');
$app->post('/password_reset','AuthController:password_reset');

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});