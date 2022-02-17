<?php

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';

// Create a service container
$container = new Container;

// Set up project dependencies
(require_once BASE_PATH . '/config/dependencies.php')($container);

// Set up project routes
(require_once BASE_PATH . '/config/routes.php')($container);

// Create the router instance
$router = $container->get(Router::class);

// Dispatch the request through the router
$response = $router->dispatch($container->get(Request::class));

// Send the response back to the browser
$response->send();


