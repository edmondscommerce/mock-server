<?php

use EdmondsCommerce\MockServer\Routing;
use Symfony\Component\HttpFoundation\Response;

require __DIR__ . '/../../src/include/routerTop.php';
require_once __DIR__ . '/../../src/bootstrap.php';

run(function (Routing\StaticRouter $router, Routing\RouteFactory $factory) {
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
    $router->addStaticRoute('/jsonfile.json', __DIR__ . '/files/jsonfile.json', 'application/json');
});