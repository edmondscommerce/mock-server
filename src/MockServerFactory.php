<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class MockServerFactory
 *
 * @package EdmondsCommerce\MockServer
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class MockServerFactory
{
    /**
     * @param string $ipAddress
     * @param int    $port
     *
     * @return MockServer
     * @throws \Exception
     */
    public function getServer(
        string $ipAddress = MockServerConfig::DEFAULT_IP,
        int $port = MockServerConfig::DEFAULT_PORT
    ): MockServer {
        return new MockServer(
            MockServerConfig::getRouterPath(),
            MockServerConfig::getHtdocsPath(),
            $ipAddress,
            $port
        );
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
