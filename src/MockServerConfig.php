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
    public const KEY_IP                  = 'MockServer_Ip';
    public const IP_LOCALHOST            = '0.0.0.0';
    public const IP_CALCULATE_NETWORK_IP = 'calculate';
    public const IP_CALCULATE_CMD        = 'ip route get 8.8.8.8 | '
                                           . 'awk \'{ for (nn=1;nn<=NF;nn++) if ($nn~"src") print $(nn+1) }\' ';
    public const DEFAULT_IP              = self::IP_LOCALHOST;

    public const KEY_PORT     = 'MockServer_Port';
    public const DEFAULT_PORT = 8080;

    public const KEY_ROUTER_PATH     = 'MockServer_RouterPath';
    public const DEFAULT_ROUTER_PATH = '/tests/MockServer/router.php';

    public const KEY_HTDOCS_PATH     = 'MockServer_HtdocsPath';
    public const DEFAULT_HTDOCS_PATH = '/tests/MockServer/htdocs';

    public const KEY_LOGS_PATH     = 'MockServer_LogsPath';
    public const DEFAULT_LOGS_PATH = __DIR__ . '/../var/logs/';


    /**
     * @return string
     * @throws MockServerException
     */
    public static function getIp(): string
    {
        $mockServerIp = $_SERVER[self::KEY_IP] ?? self::DEFAULT_IP;
        if (self::IP_CALCULATE_NETWORK_IP === $mockServerIp) {
            $mockServerIp = self::calculateMockServerIp();
        }

        return $mockServerIp;
    }

    public static function calculateMockServerIp(): string
    {
        exec(
            self::IP_CALCULATE_CMD,
            $output,
            $exitCode
        );
        if (0 !== $exitCode) {
            throw new MockServerException(
                'Failed getting mock server IP, got exit code ' . $exitCode . ' and output: '
                . "\n" . implode("\n", $output)
            );
        }

        return array_pop($output);
    }

    public static function getPort(): int
    {
        return (int)($_SERVER[self::KEY_PORT] ?? self::DEFAULT_PORT);
    }

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

            return \dirname($reflection->getFileName(), 3);
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
