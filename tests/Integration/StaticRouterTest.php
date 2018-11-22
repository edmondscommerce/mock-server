<?php

namespace EdmondsCommerce\MockServer\Tests\Integration;

use EdmondsCommerce\MockServer\MockServer;
use EdmondsCommerce\MockServer\Routing\RouteFactory;
use EdmondsCommerce\MockServer\Routing\Router;
use EdmondsCommerce\MockServer\Routing\RouterFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StaticRouterTest
 *
 * @package EdmondsCommerce\MockServer
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class StaticRouterTest extends TestCase
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var RouteFactory
     */
    private $routeFactory;

    /**
     * @throws \EdmondsCommerce\MockServer\Exception\MockServerException
     */
    public function setUp(): void
    {
        $this->routeFactory = new RouteFactory();
        $this->router       = (new RouterFactory())->make();
    }

    public function testItWillReturnTheNotFoundPageWhenNotFound(): void
    {
        $this->router->setNotFound('Not Found');
        $result = $this->router->run('/does-not-exist');
        if (null === $result) {
            throw new \Exception('response is null');
        }

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(404, $result->getStatusCode());
        $this->assertEquals('Not Found', $result->getContent());
    }

    public function testItWillMatchARoute(): void
    {
        $this->router->addRoute($this->routeFactory->textRoute('/test', 'Found it'));
        $result = $this->router->run('/test');

        $this->assertNotNull($result);
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals('Found it', $result->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testItWillDetectTheRequestUri(): void
    {
        //Fake the server request
        $_SERVER['REQUEST_URI'] = '/test';

        $this->router->addRoute($this->routeFactory->textRoute('/test', 'Detected'));

        $result = $this->router->run();

        $this->assertNotNull($result);
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals('Detected', $result->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testItEnforcesCallbackReturnType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $_SERVER['REQUEST_URI'] = '/some-callback';

        $this->router->addRoute($this->routeFactory->callbackRoute('/bad-return-type',
            function () {
                return 'this function does not have the correct return type';
            })
        );

        $this->router->run();
    }

    /**
     * @throws \Exception
     */
    public function testItCanHandleCallbackRoutes(): void
    {
        $_SERVER['REQUEST_URI'] = '/some-callback';
        $this->router->addRoute($this->routeFactory->callbackRoute('/some-callback',
            function (): Response {
                return new Response('This is a callback result');
            })
        );

        $result = $this->router->run();

        $this->assertNotNull($result);
        $this->assertEquals('This is a callback result', $result->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testItWillCreateRequestFilesOnRequest(): void
    {
        $logDir = MockServer::getLogsPath();

        $this->router->addRoute($this->routeFactory->textRoute('/', 'Test'));
        $this->router->run('/');

        $this->assertFileExists($logDir . '/' . MockServer::REQUEST_FILE);
    }

    public function testStaticRouteSetsContentType(): void
    {
        $jsonFile               = __DIR__ . '/../MockServer/files/jsonfile.json';
        $_SERVER['REQUEST_URI'] = '/jsonfile.json';
        $this->router->addRoute($this->routeFactory->staticRoute(
            '/jsonfile.json',
            $jsonFile,
            'application/json'
        ));
        $result = $this->router->run();
        if (!$result instanceof Response) {
            throw new \Exception('Failed getting a response');
        }
        $this->assertEquals('application/json', $result->headers->get('Content-Type'));
        $this->assertStringEqualsFile($jsonFile, $result->getContent());
    }
}
