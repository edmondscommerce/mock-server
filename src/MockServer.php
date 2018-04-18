<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

/**
 * Class MockServer
 *
 * @package EdmondsCommerce\MockServer
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class MockServer
{
    public const LOG_FILE      = 'mockserver.log';
    public const REQUEST_FILE  = 'request.json';
    public const RESPONSE_FILE = 'response.json';

    /**
     * @var string
     */
    private static $logsPath;

    /**
     * @var string
     */
    private $routerPath;

    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $htdocsPath;


    /**
     * MockServer constructor.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param string $routerPath
     * @param string $htdocsPath
     * @param string $ipAddress
     * @param int    $port
     *
     * @throws \Exception
     */
    public function __construct(
        string $routerPath,
        string $htdocsPath = '',
        string $ipAddress = null,
        int $port = null
    )
    {
        if (!is_file($routerPath)) {
            throw new \RuntimeException('Router file does not exist: "'.$routerPath.'"');
        }
        $this->routerPath = realpath($routerPath);

        $this->htdocsPath = trim($htdocsPath ?: \dirname($this->routerPath));
        if (!is_dir($this->htdocsPath)) {
            throw new \RuntimeException('Htdocs folder does not exist: "'.$this->htdocsPath.'"');
        }
        $this->ipAddress = trim($ipAddress ?? MockServerConfig::DEFAULT_IP);
        $this->port = $port ?? MockServerConfig::DEFAULT_PORT;
        $this->clearLogs();
    }

    public function __destruct()
    {
        $this->stopServer();
    }

    /**
     * Sets up a temporary file and returns the path to it
     *
     * @return string
     * @throws \Exception
     */
    public static function getLogsPath(): string
    {
        if (null !== self::$logsPath) {
            return self::$logsPath;
        }
        self::$logsPath = MockServerConfig::getLogsPath();
        if (!is_dir(self::$logsPath)
            && !(mkdir(self::$logsPath, 0777, true) && is_dir(self::$logsPath))
        ) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', self::$logsPath));
        }

        return self::$logsPath;
    }

    /**
     * @param bool $background
     *
     * @return string
     * @throws \Exception
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getStartCommand(bool $background = true): string
    {
        $logFilePath = self::getLogsPath().'/'.self::LOG_FILE;
        $nohup = '';
        $detatch = '';
        if (true === $background) {
            $nohup = ' nohup ';
            $detatch = ' > \''.$logFilePath.'\' 2>&1 &';
        }

        return 'cd '.$this->htdocsPath.';'
            .$nohup
            .' php '
            .' -d error_reporting=E_ALL'
            .' -d error_log=\''.$logFilePath.'\''
            .' -S '.$this->ipAddress.':'.$this->port.' '.$this->routerPath
            .$detatch;
    }


    /**
     * Start the mock web server
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @return bool
     * @throws \Exception
     */
    public function startServer(): bool
    {
        //Stop the server if it is already running
        if ($this->isServerRunning()) {
            $this->stopServer();
        }
        $this->clearLogs();

        $startCommand = $this->getStartCommand();

        exec($startCommand, $commandOutput, $exitCode);

        //Sleep to allow the web server to start, need to keep this as low as we can to ensure tests don't take forever
        //Maximum attempts to try and connect before we fail out
        $totalAttempts = 0;
        $maxTimeoutAttempts = 5;
        do {
            usleep(100000); // 0.1s
        } while (!$this->isServerRunning() && $totalAttempts++ < $maxTimeoutAttempts);

        return ($exitCode === 0);
    }

    /**
     * @throws \Exception
     */
    public function clearLogs(): void
    {
        $logsPath = self::getLogsPath();
        $files = [
            self::LOG_FILE,
            self::REQUEST_FILE,
            self::RESPONSE_FILE,
        ];
        foreach ($files as $file) {
            file_put_contents($logsPath.$file, '');
        }
    }


    /**
     * Checks if the PHP server is running
     *
     * @return bool
     * @throws \Exception
     */
    public function isServerRunning(): bool
    {
        $pid = $this->getServerPID();

        return ($pid > 0);
    }

    /**
     * Gets the PHP server's PID
     *
     * @return int
     * @throws \Exception
     */
    public function getServerPID(): int
    {
        //-x Preg matches only on exact names instead of partial match
        //-f Matches against the process name AND the arguments for us to denote the web server from other PHP processes
        $command = 'pgrep -u "$(whoami),root" -f "php.*[-]S"';

        exec($command, $outputArray, $exitCode);
        $output = implode("\n", $outputArray);

        if ($exitCode !== 0 && count($outputArray) > 1) {
            throw new \RuntimeException(
                'Unsuccessful exit code returned: '.$exitCode.', output: '
                .$outputArray
            );
        }

        if (count($outputArray) > 1) {
            exec('ps $('.$command.')', $psOutput);
            $psOutput = implode("\n", $psOutput);
            throw new \RuntimeException('Found multiple instances of the PHP server:'.$output."\nps output:".$psOutput);
        }

        //Not found
        if ($exitCode === 1 && count($outputArray) === 0) {
            return 0;
        }

        $pid = trim($output);

        if (is_numeric($pid)) {
            return (int)$pid;
        }

        throw new \RuntimeException('Could not find PID for PHP Server: '.$output);
    }

    /**
     * @throws \Exception
     */
    public function stopServer(): void
    {

        $pid = $this->getServerPID();
        if ($pid === 0) {
            return;
        }
        $command = sprintf('kill %d 2>&1', $pid);
        exec($command, $output, $resultCode);
        $output = implode("\n", $output);
        if (0 !== $resultCode) {
            if (false !== stripos($output, 'no such process')) {
                return;
            }
            throw new \RuntimeException('Failed stopping server: '.$output);
        }
    }

    public function getBaseUrl(): string
    {
        return sprintf('http://%s:%d', $this->ipAddress, $this->port);
    }

    public function getUrl($uri): string
    {
        if ('/' !== $uri[0]) {
            $uri = "/$uri";
        }

        return $this->getBaseUrl().$uri;
    }
}
