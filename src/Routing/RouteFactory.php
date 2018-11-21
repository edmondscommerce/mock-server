<?php

namespace EdmondsCommerce\MockServer\Routing;

use Symfony\Component\Routing\Route;

class RouteFactory
{
    public function route(string $uri): Route
    {
        return new Route($uri, );
    }


    public function staticRoute(): Route
    {

    }

    public function callbackRoute(): Route
    {

    }

    public function downloadRoute(): Route
    {

    }
}