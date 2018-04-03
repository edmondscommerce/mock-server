<?php
require __DIR__.'/../../src/include/routerTop.php';
/**
 * @var \EdmondsCommerce\MockServer\StaticRouter $router
 */

$router->addStaticRoute('/test.unknownExtension', __DIR__.'/htdocs/test.unknownExtension');

$router->addCallbackRoute('/callbackRoute', 'standard response', function () {
    return ' with callback';
});

$router->addRoute('/routed', 'Routed');

$router->addRoute('/admin', 'Admin Login');


require __DIR__.'/../../src/include/routerBottom.php';
