<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

class MockServer
{
    /** @var string */
    private $tmpDir;
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
     * MockServer constructor.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param string $routerPath
     * @param string $ipAddress
     * @param int $port
     * @throws \Exception
     */
    public function __construct(string $routerPath, string $ipAddress = null, int $port = null)
    {
        if (!is_file($routerPath)) {
            throw new \RuntimeException('Router file does not exist: "' . $routerPath . '"');
        }
        $this->routerPath = realpath($routerPath);

        $this->ipAddress     = ($ipAddress ?? MockServerConfig::MOCKSERVER_IP);
        $this->port   = ($port ?? MockServerConfig::MOCKSERVER_PORT);
        $this->tmpDir = static::getTempDirectory();
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
    public static function getTempDirectory(): string
    {
        $dir = sys_get_temp_dir() ?: '/tmp';
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new \RuntimeException('Could not find the tmp directory');
        }

        $dir = $dir . DIRECTORY_SEPARATOR . 'MWS';
        if (!is_dir($dir) && !mkdir($dir) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        return $dir;
    }

    /**
     * @return MockServerRequest
     * @throws \Exception
     */
    public function getRequest(): MockServerRequest
    {
        $path = $this->getRequestPath();
        if (!is_file($this->getRequestPath())) {
            throw new \RuntimeException('Could not retrieve request, no request has been made yet');
        }

        return new MockServerRequest($path);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getRequestPath(): string
    {
        $tempDirectory = $this->tmpDir;
        return $tempDirectory . DIRECTORY_SEPARATOR . 'request.json';
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
        //Get the configuration
        $path = $this->routerPath;
        $ipAddress   = $this->ipAddress;
        $port = $this->port;

        //Stop the server if it is already running
        if ($this->isServerRunning()) {
            $this->stopServer();
        }

        $this->clearRequest();

        //Does the router/directory exist?
        if (!is_file($path) && !is_dir($path)) {
            throw new \RuntimeException('The path ' . $path . ' does not exist');
        }

        //Start the server
        //The -t denotes a base directory, we do a check on the path given to see if we are working with a router or not
        $commandToExecute = sprintf(
            'nohup php -S %s:%d %s > /dev/null 2>/dev/null &',
            $ipAddress,
            $port,
            $path
        );

        exec($commandToExecute, $commandOutput, $exitCode);

        //Sleep to allow the web server to start, need to keep this as low as we can to ensure tests don't take forever
        //Maximum attempts to try and connect before we fail out
        $totalAttempts      = 0;
        $maxTimeoutAttempts = 5;
        do {
            usleep(100000); // 0.1s
        } while (!$this->isServerRunning() && $totalAttempts++ < $maxTimeoutAttempts);

        return ($exitCode === 0);
    }

    /**
     * @throws \Exception
     */
    public function clearRequest()
    {
        $requestPath = $this->getRequestPath();
        if (file_exists($requestPath)) {
            unlink($requestPath);
        }
    }


    /**
     * Checks if the PHP server is running
     *
     * @return bool
     */
    public function isServerRunning(): bool
    {
        try {
            $this->getServerPID();
        } catch (\Exception $e) {
            return false;
        }

        return true;
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
        $command = 'pgrep -u "$(whoami),root" -f "php -S"';

        exec($command, $commandOutput, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException('Unsuccessful exit code returned: '.$exitCode);
        }

        if (count($commandOutput) > 1) {
            throw new \RuntimeException('Found multiple instances of the PHP server');
        }

        if (count($commandOutput) === 0) {
            throw new \RuntimeException('No instances of PHP server are running');
        }

        $pid = trim(array_shift($commandOutput));

        if (is_numeric($pid)) {
            return (int)$pid;
        }

        throw new \RuntimeException('Could not find PID for PHP Server');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @return bool
     */
    public function stopServer(): bool
    {
        try {
            $pid = $this->getServerPID();
            $command = sprintf('kill %d', $pid);
            exec($command, $output, $resultCode);

            return ($resultCode === 0);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getBaseUrl(): string
    {
        return sprintf('http://%s:%d', $this->ipAddress, $this->port);
    }

    public function getUrl($uri): string
    {
        return $this->getBaseUrl() . $uri;
    }
}
