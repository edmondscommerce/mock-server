<?php

namespace EdmondsCommerce\MockServer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StaticRouterTest
 *
 * @package EdmondsCommerce\MockServer
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class StaticRouterTest extends TestCase
{
    /**
     * @var StaticRouter
     */
    private $router;

    public function setUp()
    {
        $this->router = Factory::getStaticRouter();
    }

    public function testItWillReturnTheNotFoundPageWhenNotFound()
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

    public function testItWillMatchARoute()
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
     * @SuppressWarnings(PHPMD.Superglobals)
     * @throws \Exception
     */
    public function testItWillDetectTheRequestUri()
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
        if (null === $result) {
            throw new \Exception('response is null');
        }

        $this->assertEquals('This is a callback result', $result->getContent());
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @throws \Exception
     */
    public function testItWillCreateRequestFilesOnRequest()
    {
        $logDir = MockServer::getLogsPath();

        $this->router->addRoute('/', 'Test');
        $this->router->run('/');

        $this->assertFileExists($logDir.'/'.MockServer::REQUEST_FILE);
    }
}
