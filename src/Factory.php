<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class Factory
 *
 * @package         EdmondsCommerce\MockServer
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Factory
{
    /**
     * @return MockServer
     * @throws \Exception
     */
    public static function getMockServer(): MockServer
    {
        return new MockServer(
            MockServerConfig::getRouterPath(),
            MockServerConfig::getHtdocsPath(),
            MockServerConfig::getIp(),
            MockServerConfig::getPort()
        );
    }

    /**
     * @return StaticRouter
     * @throws \RuntimeException
     */
    public static function getStaticRouter(): StaticRouter
    {
        return new StaticRouter(
            MockServerConfig::getHtdocsPath()
        );
    }

    /**
     * @return Request
     * @throws \RuntimeException
     */
    public static function getLastRequest(): Request
    {
        $requestPath = MockServerConfig::getLogsPath().'/'.MockServer::REQUEST_FILE;
        $serialized  = file_get_contents($requestPath);
        if ($serialized === '') {
            throw new \RuntimeException('request log file ['.$requestPath.'] is empty');
        }

        if($serialized === false)
        {
            throw new \RuntimeException('Could not read last request: '.$requestPath);
        }

        return unserialize($serialized, ['allowed_classes' => [Request::class]]);
    }
}
