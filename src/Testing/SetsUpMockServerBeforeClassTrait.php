<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer\Testing;

use EdmondsCommerce\MockServer\Factory;
use EdmondsCommerce\MockServer\MockServer;

/**
 * Trait SetupsUpMockServerBeforeClassTrait
 *
 * @package EdmondsCommerce\MockServer\Testing
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
trait SetsUpMockServerBeforeClassTrait
{

    /**
     * @var MockServer
     */
    protected static $mockServer;

    /**
     * @throws \Exception
     */
    public static function setUpBeforeClass(): void
    {
        static::setupMockServer();
    }

    /**
     * @throws \Exception
     */
    public static function tearDownAfterClass(): void
    {
        static::tearDownMockServer();
    }

    /**
     * @throws \Exception
     */
    protected static function setupMockServer(): void
    {
        static::$mockServer = Factory::getMockServer();
        static::$mockServer->startServer(false);
    }

    /**
     * @throws \Exception
     */
    protected static function tearDownMockServer(): void
    {
        static::$mockServer->stopServer();
    }
}
