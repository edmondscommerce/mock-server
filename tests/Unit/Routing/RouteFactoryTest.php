<?php

namespace EdmondsCommerce\MockServer\Tests\Unit\Routing;

use EdmondsCommerce\MockServer\Routing\RouteFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RouteFactoryTest
 *
 * @package EdmondsCommerce\MockServer\Tests\Unit
 * @covers  \EdmondsCommerce\MockServer\Routing\RouteFactory
 */
class RouteFactoryTest extends TestCase
{
    /**
     * @var RouteFactory
     */
    private $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->factory = new RouteFactory();
    }

    /**
     * @test
     * @covers \EdmondsCommerce\MockServer\Routing\RouteFactory::textRoute
     */
    public function itCanCreateATextRoute(): void
    {
        $result = $this->factory->textRoute('/test', 'Text');

        $this->assertEquals('/test', $result->getPath());
        $this->assertEquals('Text', $result->getDefault('response'));
    }

    /**
     * @test
     * @covers \EdmondsCommerce\MockServer\Routing\RouteFactory::callbackRoute
     * @throws \ReflectionException
     */
    public function itCanCreateACallbackRoute(): void
    {
        $result = $this->factory->callbackRoute(
            '/callback',
            function (Request $request): Response {

                $request->query;
                return new Response('A callback is run');
            }
        );

        /** @var Response $response */
        $response = $result->getDefault('_controller')(new Request());

        $this->assertEquals('/callback', $result->getPath());
        $this->assertEquals('A callback is run', $response->getContent());
    }

    /**
     * @test
     * @covers \EdmondsCommerce\MockServer\Routing\RouteFactory::downloadRoute
     */
    public function itCanCreateADownloadRoute(): void
    {
        $file   = __DIR__ . '/../../MockServer/files/jsonfile.json';
        $result = $this->factory->downloadRoute('/download', $file);

        /** @var BinaryFileResponse $response */
        $response = $result->getDefault('_controller')(new Request());

        $this->assertEquals('jsonfile.json', $response->getFile()->getFilename());
        $this->assertEquals('/download', $result->getPath());
    }

    /**
     * @test
     * @covers \EdmondsCommerce\MockServer\Routing\RouteFactory::formRoute
     */
    public function itCanCreateAFormRoute(): void
    {
        $route = $this->factory->formRoute('/form', 'POST', '/success');

        $this->assertContains('POST', $route->getMethods());
        $this->assertEquals('/form', $route->getPath());
        $this->assertInstanceOf(Response::class, $route->getDefault('response'));
    }

    /**
     * @test
     * @covers \EdmondsCommerce\MockServer\Routing\RouteFactory::attemptFileRead
     * @expectedException \EdmondsCommerce\MockServer\Exception\RouterException
     * @expectedExceptionMessageRegExp /File at path .+ does not exist/
     */
    public function itWillThrowAnExceptionWhenAFileResourceDoesNotExist(): void
    {
        $badFile = __DIR__ . '/not-there.dat';

        $this->factory->downloadRoute('/nope', $badFile);
    }
}
