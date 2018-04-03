<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

use Guzzle\Http\Client;
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
    public function testCanGetLastRequestAndResponse()
    {
        $mockServer = Factory::getMockServer();
        $mockServer->startServer();
        $url    = $mockServer->getUrl('/admin');
        $client = new Client();
        $client->createRequest('GET', $url)->send();
        $request  = Factory::getLastRequest();
        $response = Factory::getLastResponse();
        $this->assertEquals('/admin', $request->getRequestUri());
        $this->assertEquals('Admin Login', $response->getContent());
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
