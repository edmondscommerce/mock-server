<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer\Testing;

use EdmondsCommerce\MockServer\MockServerFactory;
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
     *
     * @throws \Exception
     */
    protected function setupMockServer(): void
    {
        $this->mockServer = (new MockServerFactory())->getServer();
        $this->mockServer->startServer();
    }

    protected function setupMockServerWithXdebug(): void
    {
        $this->mockServer = (new MockServerFactory())->getServer();
        $this->mockServer->startServer(true);
    }

    /**
     * @throws \Exception
     */
    protected function tearDownMockServer(): void
    {
        $this->mockServer->stopServer();
    }
}
