<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer\Testing;

use EdmondsCommerce\MockServer\Factory;
use EdmondsCommerce\MockServer\MockServer;

/**
 * Trait MockServerTrait
 *
 * @package EdmondsCommerce\MockServer\Testing
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
trait MockServerTrait
{

    /**
     * @var MockServer
     */
    protected $mockServer;


    /**
     * @param bool $xdebug
     *
     * @throws \Exception
     */
    protected function setupMockServer(bool $xdebug = false): void
    {
        $this->mockServer = Factory::getMockServer();
        $this->mockServer->startServer($xdebug);
        usleep(200);
    }

    /**
     * @throws \Exception
     */
    protected function tearDownMockServer(): void
    {
        $this->mockServer->stopServer();
    }
}
