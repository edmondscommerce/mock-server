<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
ini_set('display_errors', 1);


$router = new EdmondsCommerce\MockServer\StaticRouter();
$router->addStaticRoute('/', __DIR__."/../html/test.html");
$router->addRoute('/admin', 'Admin Login');
$router->setNotFound('Not found');
$router->run()->send();
