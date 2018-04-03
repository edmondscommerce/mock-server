<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

use Composer\Autoload\ClassLoader;
use EdmondsCommerce\MockServer\Exception\MockServerException;

/**
 * Class MockServerConfig
 *
 * @package EdmondsCommerce\MockServer
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class MockServerConfig
{
    const KEY_IP     = 'MockServer_Ip';
    const DEFAULT_IP = '0.0.0.0';

    const KEY_PORT     = 'MockServer_Port';
    const DEFAULT_PORT = 8080;

    const KEY_ROUTER_PATH     = 'MockServer_RouterPath';
    const DEFAULT_ROUTER_PATH = '/tests/MockServer/router.php';

    const KEY_HTDOCS_PATH     = 'MockServer_HtdocsPath';
    const DEFAULT_HTDOCS_PATH = '/tests/MockServer/htdocs';

    const KEY_LOGS_PATH     = 'MockServer_LogsPath';
    const DEFAULT_LOGS_PATH = __DIR__.'/../var/logs/';


    public static function getIp(): string
    {
        return $_SERVER[self::KEY_IP] ?? self::DEFAULT_IP;
    }

    public static function getPort(): int
    {
        return (int)($_SERVER[self::KEY_PORT] ?? self::DEFAULT_PORT);
    }

    public static function getRouterPath(): string
    {
        return $_SERVER[self::KEY_ROUTER_PATH] ?? self::getPathToProjectRoot().'/'.self::DEFAULT_ROUTER_PATH;
    }

    public static function getHtdocsPath(): string
    {
        return $_SERVER[self::KEY_HTDOCS_PATH] ?? self::getPathToProjectRoot().'/'.self::DEFAULT_HTDOCS_PATH;
    }

    public static function getLogsPath(): string
    {
        return $_SERVER[self::KEY_LOGS_PATH] ?? self::DEFAULT_LOGS_PATH;
    }

    /**
     * @return string
     * @throws MockServerException
     */
    public static function getPathToProjectRoot(): string
    {
        try {
            $reflection = new \ReflectionClass(ClassLoader::class);

            return \dirname($reflection->getFileName(), 3);
        } catch (\Exception $e) {
            throw new MockServerException('Exception in '.__METHOD__, $e->getCode(), $e);
        }
    }
}
