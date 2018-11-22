<?php

namespace EdmondsCommerce\MockServer\Routing;

use EdmondsCommerce\MockServer\MockServerConfig;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouterFactory
 *
 * @package EdmondsCommerce\MockServer\Routing
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class RouterFactory
{
    /**
     * @param string|null $publicDir
     *
     * @return Router
     * @throws \EdmondsCommerce\MockServer\Exception\MockServerException
     */
    public function make(string $publicDir = null): Router
    {
        if ($publicDir === null) {
            $publicDir = MockServerConfig::getHtdocsPath();
        }

        return new Router($publicDir, new RouteCollection());
    }
}