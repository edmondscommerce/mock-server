<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

use Guzzle\Http\Client;
use PHPUnit\Framework\TestCase;

/**
 * Class MockServerTest
 * This class is purely for ensuring that the test methods work on the MockServerTrait trait
 *
 */
class MockServerTest extends TestCase
{
    /**
     * @var MockServer
     */
    private $server;


    /**
     * @throws \Exception
     */
    public function setUp()
    {
        parent::setUp();

        //Remove all MWS directories for a clean run
        exec('rm -rf '.MockServer::getTempDirectory());

        $this->server = new MockServer(__DIR__.'/assets/MockServerTest/router.php');
    }

    public function tearDown()
    {
        parent::tearDown();

        //Stop the server before rerunning the next test and to stop the process being left behind
        $this->server->stopServer();
    }

    /**
     * @skip
     * @throws \Exception
     */
    public function testItWillHandleARoutingFile()
    {
        $this->server->startServer();

        $url = $this->server->getUrl('/');

        $this->server->startServer();

        $client = new Client();
        $response = $client->createRequest('GET', $url)->send();
        $html = $response->getBody(true);

        $this->assertEquals('Routed Content', $html);
    }

    /**
     * @throws \Exception
     */
    public function testItWillHandleFriendlyUrls()
    {
        $this->server->startServer();
        $url = $this->server->getUrl('/admin');

        $this->server->startServer();

        $client = new Client();
        $response = $client->createRequest('GET', $url)->send();
        $html = $response->getBody(true);

        $this->assertEquals('Admin Login', $html);
    }

    /**
     * @throws \Exception
     */
    public function testItCanGetTheRequest()
    {
        $this->server->startServer();
        $url = $this->server->getUrl('/');
        file_get_contents($url);

        $request = $this->server->getRequest();

        $this->assertInstanceOf(MockServerRequest::class, $request);
    }

    public function testItWillClearTheRequestOnStart()
    {
        $requestFile = MockServer::getTempDirectory().'/request.json';
        touch($requestFile);

        $this->server->startServer();

        $this->assertFileNotExists($requestFile);
    }

    public function testItWillErrorOnWhenTryingToGetTheRequestBeforeReceivingARequest()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not retrieve request, no request has been made yet');

        $this->server->startServer();
        $this->server->getRequest();
    }

}