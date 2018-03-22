<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
ini_set('display_errors', 1);

/**
 * Make sure to add this bit, to serve all the specified extensions without creating a route for them
 */
if (preg_match('/\.(?:png|css|jpg|jpeg|gif|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
}

$router = new EdmondsCommerce\MockServer\StaticRouter();
$router->addStaticRoute('/', __DIR__."/../html/test.html");
$router->addRoute('/admin', 'Admin Login');
$router->setNotFound('Not found');
$router->run()->send();
