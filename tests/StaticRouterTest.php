<?php

namespace EdmondsCommerce\MockServer;

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
     * @var StaticRouter
     */
    private $router;

    public function setUp(): void
    {
        $this->router = Factory::getStaticRouter();
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
        $this->router->addRoute('/test', 'Found it');
        $result = $this->router->run('/test');
        if (null === $result) {
            throw new \Exception('response is null');
        }
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

        $this->router->addRoute('/test', 'Detected');

        $result = $this->router->run();
        if (null === $result) {
            throw new \Exception('response is null');
        }

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

        $this->router->addCallbackRoute('/bad-return-type',
            function () {
                return 'this function does not have the correct return type';
            });

        $this->router->run();
    }

    /**
     * @throws \Exception
     */
    public function testItCanHandleCallbackRoutes(): void
    {
        $_SERVER['REQUEST_URI'] = '/some-callback';

        $this->router->addCallbackRoute('/some-callback',
            function (): Response {
                return new Response('This is a callback result');
            });

        $result = $this->router->run();
        if (null === $result) {
            throw new \Exception('response is null');
        }

        $this->assertEquals('This is a callback result', $result->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testItWillCreateRequestFilesOnRequest(): void
    {
        $logDir = MockServer::getLogsPath();

        $this->router->addRoute('/', 'Test');
        $this->router->run('/');

        $this->assertFileExists($logDir . '/' . MockServer::REQUEST_FILE);
    }

    public function testStaticRouteSetsContentType(): void
    {
        $jsonFile               = __DIR__ . '/MockServer/files/jsonfile.json';
        $_SERVER['REQUEST_URI'] = '/jsonfile.json';
        $this->router->addStaticRoute(
            '/jsonfile.json',
            $jsonFile,
            'application/json'
        );
        $result = $this->router->run();
        if (!$result instanceof Response) {
            throw new \Exception('Failed getting a response');
        }
        $this->assertEquals('application/json', $result->headers->get('Content-Type'));
        $this->assertStringEqualsFile($jsonFile, $result->getContent());
    }
}
