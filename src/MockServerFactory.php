<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

use EdmondsCommerce\MockServer\Routing\Router;
use EdmondsCommerce\MockServer\Routing\RouterFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MockServerFactory
 *
 * @package EdmondsCommerce\MockServer
 */
class MockServerFactory
{
    /**
     * @return MockServer
     * @throws \Exception
     */
    public function getServer(): MockServer
    {
        return new MockServer(
            MockServerConfig::getRouterPath(),
            MockServerConfig::getHtdocsPath(),
            MockServerConfig::getIp(),
            MockServerConfig::getPort()
        );
    }

    /**
     * @return Router
     * @throws \RuntimeException
     * @throws Exception\MockServerException
     */
    public function getRouter(): Router
    {
        return (new RouterFactory())->make(MockServerConfig::getHtdocsPath());
    }

    /**
     * @return Request
     * @throws \RuntimeException
     */
    public static function getLastRequest(): Request
    {
        $requestPath = MockServerConfig::getLogsPath() . '/' . MockServer::REQUEST_FILE;
        $serialized  = file_get_contents($requestPath);
        if ($serialized === '') {
            throw new \RuntimeException('request log file [' . $requestPath . '] is empty');
        }

        if ($serialized === false) {
            throw new \RuntimeException('Could not read last request: ' . $requestPath);
        }

        return unserialize($serialized, ['allowed_classes' => [Request::class]]);
    }
}
