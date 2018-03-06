<?php

namespace EdmondsCommerce\MockServer;

use EdmondsCommerce\MockServer\Exception\RouterException;
use http\Env\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class StaticRouterTest extends TestCase
{
    /**
     * @var StaticRouter
     */
    private $router;

    public function setUp()
    {
        $this->router = new StaticRouter();
    }

    public function testItWillThrowAnExceptionOnNoRouteWithNo404Defined()
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('No 404 response defined');

        $this->router->run('/');
    }

    public function testItWillReturnTheNotFoundPageWhenNotFound()
    {
        $this->router->setNotFound('Not Found');
        $result = $this->router->run('/');

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(404, $result->getStatusCode());
        $this->assertEquals('Not Found', $result->getContent());
    }

    public function testItWillMatchARoute()
    {
        $this->router->addRoute('/test', 'Found it');
        $result = $this->router->run('/test');

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals('Found it', $result->getContent());
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @throws \Exception
     */

    public function testItWillDetectTheRequestUri()
    {
        //Fake the server request
        $_SERVER['REQUEST_URI'] = '/test';

        $this->router->addRoute('/test', 'Detected');

        $result = $this->router->run();

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals('Detected', $result->getContent());
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @throws \Exception
     */

    public function testItCanHandleCallbackRoutes()
    {
        $_SERVER['REQUEST_URI'] = '/some-callback';

        $this->router->addCallbackRoute('/some-callback', '', function () {
            return 'This is a callback result';
        });

        $result = $this->router->run();

        $this->assertEquals('This is a callback result', $result->getContent());
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @throws \Exception
     */
    public function testItWillCreateRequestFilesOnRequest()
    {
        $logDir = MockServer::getTempDirectory();

        $this->router->addRoute('/', 'Test');
        $this->router->run('/');

        $this->assertFileExists($logDir.'/request.json');
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @throws \Exception
     */

    public function testTheRequestFileWillContainRequiredValues()
    {
        $logDir = MockServer::getTempDirectory();
        $this->router->addRoute('/', 'Body output');

        $_SERVER['REQUEST_URI'] = '/';
        $_POST = ['test' => 1, 'another' => ['a', 'b', 'c']];
        $_GET = ['page' => 1, 'limit' => 10];

        $this->router->run();
        $response = @file_get_contents($logDir.'/request.json');

        $this->assertInternalType('string', $response);
        $this->assertEquals([
            'post' => $_POST,
            'get' => $_GET
        ], json_decode($response, true));
    }
}
