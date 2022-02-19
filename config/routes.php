<?php

use Illuminate\Container\Container;
use Illuminate\Routing\Router;
use Illuminate\Http\Response;
use App\Controller\InvoiceController;

/**
 * Setup routes application routes
 * @param Container $container
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */
return static function (Container $container) {
    $router = $container->get(Router::class);

    // Serving react app
    $router->get('/', function () {
        return file_get_contents(BASE_PATH . '/resources/index.html');
    });

    // Csv upload route
    $router->post('invoice/create', [InvoiceController::class, 'create']);

    // Return 404 for when there is no match
    $router->any('{any}', function (Response $response) {
        return $response->setStatusCode(404);
    })->where('any', '(.*)');
};
