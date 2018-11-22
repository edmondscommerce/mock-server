<?php declare(strict_types=1);

/**
 * Ensure we are displaying all errors - these will show up in the logs
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);

$files = [
    __DIR__ . '/../../../../autoload.php',
    __DIR__ . '/../vendor/autoload.php',
];

$autoloadFileFound = false;
foreach ($files as $file) {
    if (file_exists($file)) {
        require $file;
        $autoloadFileFound = true;
        break;
    }
}

if (!$autoloadFileFound) {
    echo 'You need to set up the project dependencies using the following commands:' . PHP_EOL .
         'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
         'php composer.phar install' . PHP_EOL;
    die(1);
}

/**
 * @param callable $runnable
 *
 * @return bool
 * @throws Exception
 */
function run(callable $runnable)
{
    $router = \EdmondsCommerce\MockServer\Factory::getStaticRouter();
    $routeFactory = new \EdmondsCommerce\MockServer\Routing\RouteFactory();
    $runnable($router, $routeFactory);

    /**
     * @var $router \EdmondsCommerce\MockServer\Routing\StaticRouter
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
}