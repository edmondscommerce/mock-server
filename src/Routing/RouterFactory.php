<?php

namespace EdmondsCommerce\MockServer\Routing;

use EdmondsCommerce\MockServer\MockServerConfig;
use Symfony\Component\Routing\RouteCollection;

class RouterFactory
{
    /**
     * @param string|null $publicDir
     *
     * @return StaticRouter
     * @throws \EdmondsCommerce\MockServer\Exception\MockServerException
     */
    public function make(string $publicDir = null): StaticRouter
    {
        if ($publicDir === null) {
            $publicDir = MockServerConfig::getHtdocsPath();
        }

        return new StaticRouter($publicDir, new RouteCollection());
    }
}