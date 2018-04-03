<?php declare(strict_types=1);
/**
 * @var $router \EdmondsCommerce\MockServer\StaticRouter
 */
$response = $router->run();
/**
 * Static assets, we just return false and allow them to be served statically
 */
if (null === $response) {
    return false;
}
/**
 * Otherwise lets send the response
 */
$response->send();
