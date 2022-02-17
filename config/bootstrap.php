<?php

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Router;
use Illuminate\Translation\Translator;
use Illuminate\Translation\FileLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Validation\Factory;

/**
 * Setup application dependencies
 * @param Container $container
 */
return static function (Container $container) {
    // Binds request to the service container as singleton and captures it
    $container->singleton(Request::class, function() {
        return Request::capture();
    });

    // Binds Router to the service container as singleton
    $container->singleton(Router::class, function (Container $container) {
        return new Router($container->get(Dispatcher::class), $container);
    });

    // Bind Validation Factory to the service container
    $container->bind('Illuminate\Translation\Translator', function (Container $container) {
        $loader = $container->make(FileLoader::class, [
            'files' => $container->get(Filesystem::class),
            'path' => BASE_PATH . '/lang'
        ]);
        return new Translator($loader, 'en');
    });

    // Bind Translator to the service container
    $container->bind('Illuminate\Validation\Factory', function (Container $container) {
        $translator = $container->make(Translator::class, [
            'loader' => $container->make(FileLoader::class, [
                'files' => $container->get(Filesystem::class),
                'path' => BASE_PATH . '/lang'
            ]),
            'locale' => 'en'
        ]);

        return new Factory($translator);
    });

    // Bind Translator to the service container
    $container->bind('validator', function (Container $container) {
        $translator = $container->make(Translator::class, [
            'loader' => $container->make(FileLoader::class, [
                'files' => $container->get(Filesystem::class),
                'path' => BASE_PATH . '/lang'
            ]),
            'locale' => 'en'
        ]);

        return new Factory($translator);
    });
};
