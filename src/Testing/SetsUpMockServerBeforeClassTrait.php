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
    public static function setupBeforeClass()
    {
        static::setupMockServer();
    }

    public static function tearDownAfterClass()
    {
        static::tearDownMockServer();
    }

    /**
     * @throws \Exception
     */
    protected static function setupMockServer()
    {
        static::$mockServer = Factory::getMockServer();
        static::$mockServer->startServer();
    }

    protected static function tearDownMockServer()
    {
        static::$mockServer->stopServer();
    }
}
