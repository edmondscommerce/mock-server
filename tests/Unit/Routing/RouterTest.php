<?php

namespace EdmondsCommerce\MockServer\Tests\Unit\Routing;

use EdmondsCommerce\MockServer\Routing\RouterFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class RouterTest
 *
 * @package EdmondsCommerce\MockServer\Tests\Unit
 * @covers \EdmondsCommerce\MockServer\Routing\Router
 */
class RouterTest extends TestCase
{

    /**
     * @test
     * @covers \EdmondsCommerce\MockServer\Routing\Router::setVerbose
     * @throws \EdmondsCommerce\MockServer\Exception\MockServerException
     */
    public function itWillAllowVerboseSetting():void
    {
        $router = (new RouterFactory())->make();

        $router->setVerbose(true);
        $this->assertTrue($router->isVerbose());
    }
}
