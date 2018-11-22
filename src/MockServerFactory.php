<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class MockServerFactory
 *
 * @package EdmondsCommerce\MockServer
 */
class MockServerFactory
{
    /**
     * @param string $ip
     * @param int    $port
     *
     * @return MockServer
     * @throws \Exception
     */
    public function getServer(
        string $ip = MockServerConfig::DEFAULT_IP,
        int $port = MockServerConfig::DEFAULT_PORT
    ): MockServer {
        return new MockServer(
            MockServerConfig::getRouterPath(),
            MockServerConfig::getHtdocsPath(),
            $ip,
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
