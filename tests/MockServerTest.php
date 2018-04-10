<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

use EdmondsCommerce\MockServer\Testing\SetsUpMockServerBeforeClassTrait;
use Guzzle\Http\Client;
use PHPUnit\Framework\TestCase;

/**
 * Class MockServerTest
 * This class is purely for ensuring that the test methods work on the MockServerTrait trait
 *
 */
class MockServerTest extends TestCase
{
    use SetsUpMockServerBeforeClassTrait;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @skip
     * @throws \Exception
     */
    public function testItWillHandleARoutingFile()
    {
        $url = static::$mockServer->getUrl('/routed');

        $client   = new Client();
        $response = $client->createRequest('GET', $url)->send();
        $html     = $response->getBody(true);

        $this->assertEquals('Routed', $html);
    }

    /**
     * @throws \Exception
     */
    public function testItWillHandleFriendlyUrls()
    {

        $url = static::$mockServer->getUrl('/admin');

        $client   = new Client();
        $response = $client->createRequest('GET', $url)->send();
        $html     = $response->getBody(true);

        $this->assertEquals('Admin Login', $html);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @throws \Exception
     */

    public function testItWillClearTheRequestOnStart()
    {
        $requestFile = MockServer::getLogsPath().'/'.MockServer::REQUEST_FILE;
        touch($requestFile);

        static::$mockServer->startServer();
        $contents = file_get_contents($requestFile);
        $this->assertEmpty($contents, 'request file contains: '.$contents);
    }

    public function testItServesDownloadRoutes()
    {
        $url                = static::$mockServer->getUrl('/download');
        $client             = new Client();
        $response           = $client->createRequest('GET', $url)->send();
        $contentDisposition = $response->getContentDisposition();
        $this->assertEquals($contentDisposition, $contentDisposition);
    }
}
