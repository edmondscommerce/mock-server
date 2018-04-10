<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Class FactoryTest
 *
 * @package EdmondsCommerce\MockServer
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FactoryTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testCanGetMockServer()
    {
        Factory::getMockServer();
        $this->assertTrue(true);
    }

    /**
     * @throws \Exception
     */
    public function testCanGetstaticRouter()
    {
        Factory::getStaticRouter();
        $this->assertTrue(true);
    }

    /**
     * @throws \Exception
     */
    public function testCanGetLastRequest()
    {
        $mockServer = Factory::getMockServer();
        $mockServer->startServer();
        $url    = $mockServer->getUrl('/admin');
        $client = new Client();
        $client->request('GET', $url);
        $request  = Factory::getLastRequest();
        $this->assertEquals('/admin', $request->getRequestUri());
    }


    public function testItWillErrorOnWhenTryingToGetTheRequestBeforeReceivingARequest()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('request log file is empty');
        $mockServer = Factory::getMockServer();
        $mockServer->startServer();
        Factory::getLastRequest();
    }
}
