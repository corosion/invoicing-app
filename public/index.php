<?php

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use Illuminate\Validation\ValidationException;

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';

// Register whoops error handler
(new Run)->pushHandler(new PrettyPageHandler)->register();

// Create a service container
$container = Container::getInstance();
Facade::setFacadeApplication($container);

// Set up project bootstrap dependencies
(require_once BASE_PATH . '/config/bootstrap.php')($container);

// Set up project routes
(require_once BASE_PATH . '/config/routes.php')($container);

// Create the router instance
$router = $container->get(Router::class);

// Create the request instance
$request = $container->get(Request::class);

// Dispatch the request through the router
try {
    $response = $router->dispatch($request);
} catch (ValidationException $e) {
    $response = Router::toResponse(
        $request,
        $container->get(JsonResponse::class)
            ->setStatusCode(Response::HTTP_BAD_REQUEST)
            ->setData([
                'errors' => $e->errors()
            ])
    );
}

// Send the response back to the browser
$response->send();


