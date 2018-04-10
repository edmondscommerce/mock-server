<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $serialized = file_get_contents(
            MockServerConfig::getLogsPath().'/'.MockServer::REQUEST_FILE
        );
        if (empty($serialized)) {
            throw new \RuntimeException('request log file is empty');
        }

        return unserialize($serialized, ['allowed_classes' => [Request::class]]);
    }

}
