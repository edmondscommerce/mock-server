<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer\Tests\Integration;

use EdmondsCommerce\MockServer\MockServerFactory;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Class FactoryTest
 *
 * @package EdmondsCommerce\MockServer
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class MockServerFactoryTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testCanGetMockServer():void
    {
        $server = (new MockServerFactory())->getServer();

        $this->addToAssertionCount(1);
    }


    /**
     * @test
     */
    public function itCanGetLastRequest():void
    {
        $mockServer = (new MockServerFactory())->getServer();
        $mockServer->startServer();
        $url    = $mockServer->getUrl('/admin');
        $client = new Client();
        $client->request('GET', $url);
        $request  = MockServerFactory::getLastRequest();
        $this->assertEquals('/admin', $request->getRequestUri());
    }

    /**
     * @throws \Exception
     * @test
     */
    public function itWillErrorWhenTryingToGetTheRequestBeforeReceivingARequest():void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('%request log file .+? is empty%');
        $mockServer = (new MockServerFactory())->getServer();
        $mockServer->startServer();
        MockServerFactory::getLastRequest();
    }
}
