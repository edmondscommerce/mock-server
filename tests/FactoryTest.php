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
    public function testCanGetMockServer():void
    {
        Factory::getMockServer();

        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Exception
     */
    public function testCanGetstaticRouter():void
    {
        Factory::getStaticRouter();
        $this->addToAssertionCount(1);
    }

    public function testCanGetLastRequest():void
    {
        $mockServer = Factory::getMockServer();
        $mockServer->startServer();
        $url    = $mockServer->getUrl('/admin');
        $client = new Client();
        $client->request('GET', $url);
        $request  = Factory::getLastRequest();
        $this->assertEquals('/admin', $request->getRequestUri());
    }


    public function testItWillErrorOnWhenTryingToGetTheRequestBeforeReceivingARequest():void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('%request log file .+? is empty%');
        $mockServer = Factory::getMockServer();
        $mockServer->startServer();
        Factory::getLastRequest();
    }
}
