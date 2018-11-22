<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

use Composer\Autoload\ClassLoader;
use EdmondsCommerce\MockServer\Exception\MockServerException;
use JakubOnderka\PhpParallelLint\RunTimeException;

/**
 * Class MockServerConfig
 *
 * @package EdmondsCommerce\MockServer
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class MockServerConfig
{
    public const DEFAULT_IP   = '0.0.0.0';
    public const DEFAULT_PORT = 8080;

    public const KEY_ROUTER_PATH     = 'MockServer_RouterPath';
    public const DEFAULT_ROUTER_PATH = '/tests/MockServer/router.php';

    public const KEY_HTDOCS_PATH     = 'MockServer_HtdocsPath';
    public const DEFAULT_HTDOCS_PATH = '/tests/MockServer/htdocs';

    public const KEY_LOGS_PATH     = 'MockServer_LogsPath';
    public const DEFAULT_LOGS_PATH = __DIR__ . '/../var/logs/';

    public static function getRouterPath(): string
    {
        return $_SERVER[self::KEY_ROUTER_PATH] ?? self::getPathToProjectRoot() . '/' . self::DEFAULT_ROUTER_PATH;
    }

    /**
     * @return string
     * @throws MockServerException
     */
    public static function getPathToProjectRoot(): string
    {
        try {
            $reflection = new \ReflectionClass(ClassLoader::class);

            $fileName = $reflection->getFileName();
            if ($fileName === false) {
                throw new RunTimeException('Can not get file name of core PHP class');
            }

            return \dirname($fileName, 3);
        } catch (\Exception $e) {
            throw new MockServerException('Exception in ' . __METHOD__, $e->getCode(), $e);
        }
    }

    public static function getHtdocsPath(): string
    {
        return $_SERVER[self::KEY_HTDOCS_PATH] ?? self::getPathToProjectRoot() . '/' . self::DEFAULT_HTDOCS_PATH;
    }

    public static function getLogsPath(): string
    {
        return $_SERVER[self::KEY_LOGS_PATH] ?? self::DEFAULT_LOGS_PATH;
    }
}
