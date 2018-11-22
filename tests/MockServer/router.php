<?php

declare(strict_types=1);

use EdmondsCommerce\MockServer\Routing;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../../src/bootstrap.php';

run(function (Routing\Router $router, Routing\RouteFactory $factory) {
    $router->addRoute($factory->staticRoute('/test.unknownExtension', __DIR__ . '/htdocs/test.unknownExtension'));
    $router->addRoute($factory->callbackRoute(
        '/callbackRoute',
        function (): Response {
            return new Response('callback response');
        })
    );

    $router->addRoute($factory->textRoute('/routed', 'Routed'));
    $router->addRoute($factory->textRoute('/admin', 'Admin Login'));
    $router->addRoute($factory->downloadRoute('/download', __DIR__ . '/files/downloadfile.extension'));
    $router->addRoute($factory->staticRoute('/jsonfile.json', __DIR__ . '/files/jsonfile.json', 'application/json'));
});