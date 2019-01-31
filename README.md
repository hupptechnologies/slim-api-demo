# Slim Framework 3 Skeleton Api Application

Use this skeleton application to quickly setup and start working on a new Slim Framework 3 Api application. This application uses the latest Slim 3 with the PHP-View template renderer. It also uses the Monolog logger.

This skeleton application was built for Composer. This makes setting up a new Slim Framework application quick and easy.

## Install the Application

First clone application

Go to the clone directory and run composer install or composer update.

	composer install


* Ensure `logs/` is web writeable.

In ubuntu permission issues run following command:

    sudo chmod 777 logs/app.log

To run the application in development, you can run these commands 

	cd [my-app-name]
	php composer.phar start

Run this command in the application directory to run the test suite

	php composer.phar test

Run this command in the application directory for Eloquent

	composer require illuminate/database

That's it! Now go build something cool.


## Code Overview

Open the project directory using your favorite editor.The app follows the structure of Slim skeleton application with minor changes. The skeleton is a good starting point when developing with Slim framework.We are use main folder app.In app folder we have controller,model and middleware folder. we have validator and mail file in this folder.

## Container Dependencies and Services

In different parts of the application we need to use other classes and services. These classes and services also depends on other classes. Managing these dependencies becomes easier when we have a container to hold them. Basically, we configure these classes and store them in the container. Later, when we need a service or a class we ask the container, and it will instantiate the class based on our configuration and return it.

The container is configured in the src/dependencies.php. We start be retrieving the container from the $app instance and configure the required services:
```php
	$container = $app->getContainer();
    
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new Monolog\Logger($settings['name']);
        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    
        return $logger;
    };
```
we have also define two controller and db as below:
```php
	$capsule = new \Illuminate\Database\Capsule\Manager;
	$capsule->addConnection($container['settings']['db']);
	$capsule->setAsGlobal();
	$capsule->bootEloquent();

	$container['db'] = function ($container) use ($capsule) {
	    return $capsule;
	};

	$container['AuthController'] = function ($container)
	{
	    return new \App\Controllers\AuthController;
	};

	$container['UserUpdateController'] = function ($container)
	{
	    return new \App\Controllers\UserUpdateController;
	};
```
## Request-Response Cycle

All requests go through the same cycle: routing > middleware > conroller > response

#### Route:

All the app routes are defined in the src/routes.php file.In route file we have also use group routing or single route as below: 
```php
	$app->post('/login','AuthController:login');
```
#### Middleware:

In a Slim app, you can add middleware to all incoming routes, to a specific route, or to a group of routes. Middleware file are in app folder directory. In this app we are create middleware for login auth.In this app we add some middleware to specific routes. for example:
```php
	$app->group('/user', function () {
		$this->get('/logout','AuthController:logout');
		$this->post('/update_profile','UserUpdateController:update_profile');
		$this->post('/update_profile_photo','UserUpdateController:update_profile_photo');
		$this->post('/push_token','UserUpdateController:push_token');
	})->add(new AuthMiddleware($container));
```
#### Controllers

After passing through all assigned middleware, the request will be processed by a controller.
The controller's job is to validate the request data, check for authorization, process the request by calling a model or do other jobs, and eventually return a response in the form of JSON response.

## Authentication and Security

#### Generating The Token:

We generate the Token when the user login using his email/password. This is done in the AuthController.
However, in a bigger application you might want to implement more authorization system.
Finally, we send the token with the response back to the user/client.