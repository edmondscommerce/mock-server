<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer\Tests\Integration;

use EdmondsCommerce\MockServer\MockServer;
use EdmondsCommerce\MockServer\Testing\MockServerTrait;
use GuzzleHttp\Client;
use JakubOnderka\PhpParallelLint\RunTimeException;
use PHPUnit\Framework\TestCase;

/**
 * Class MockServerTest
 * This class is purely for ensuring that the test methods work on the MockServerTrait trait
 *
 */
class MockServerTest extends TestCase
{
    use MockServerTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupMockServer(true);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->tearDownMockServer();
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @skip
     */
    public function testItWillHandleARoutingFile(): void
    {
        $url = $this->mockServer->getUrl('/routed');

        $client   = new Client();
        $response = $client->request('GET', $url);
        $html     = $response->getBody();

        $this->assertEquals('Routed', $html);
    }

    public function testItWillHandleFriendlyUrls(): void
    {
        $url = $this->mockServer->getUrl('/admin');

        $client   = new Client();
        $response = $client->request('GET', $url);
        $html     = $response->getBody()->getContents();

        $this->assertEquals('Admin Login', $html);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @throws \Exception
     */
    public function testItWillClearTheRequestOnStart(): void
    {
        $requestFile = MockServer::getLogsPath() . '/' . MockServer::REQUEST_FILE;
        touch($requestFile);

        $this->mockServer->startServer();
        $contents = file_get_contents($requestFile);
        $this->assertEmpty($contents, 'request file contains: ' . $contents);
    }

    public function testItServesDownloadRoutes(): void
    {
        $url      = $this->mockServer->getUrl('/download');
        $client   = new Client();
        $buffer   = fopen('php://temp', 'w');
        $response = $client->request(
            'GET',
            $url,
            [
                'save_to'     => $buffer,
                'synchronous' => true,
            ]
        );
        $this->assertEquals(
            'attachment; filename=downloadfile.extension',
            current($response->getHeader('Content-Disposition'))
        );
        if (!is_resource($buffer)) {
            throw new RunTimeException('Expected a resource');
        }
        rewind($buffer);
        $contents = fread($buffer, 9999);
        if (!is_string($contents)) {
            throw new RunTimeException('Error reading from resource');
        }
        $this->assertNotEmpty($contents);
        $this->assertEquals('this is a download file', trim($contents));
        fclose($buffer);
    }

    public function testItServesStaticJsonRoutes(): void
    {
        $jsonFile = __DIR__ . '/../MockServer/files/jsonfile.json';
        $url      = $this->mockServer->getUrl('jsonfile.json');
        $client   = new Client();

        $response = $client->request('GET', $url, ['synchronous' => true]);

        $this->assertEquals('application/json', current($response->getHeader('Content-Type')));
        $this->assertStringEqualsFile($jsonFile, $response->getBody()->getContents());
    }
}
