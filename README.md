# Mock Web Server and Router 
## By [Edmonds Commerce](https://www.edmondscommerce.co.uk)

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a791bb0914a243749b3c9918c70af2da)](https://www.codacy.com/app/edmondscommerce/mock-server?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=edmondscommerce/mock-server&amp;utm_campaign=Badge_Grade) [![Build Status](https://travis-ci.org/edmondscommerce/mock-server.svg?branch=master)](https://travis-ci.org/edmondscommerce/mock-server)

## Installation

Install via composer

`composer require edmondscommerce/mock-server:dev-master@dev --dev`

## Usage
To start the web server, you need to call instantiate the `\EdmondsCommerce\MockServer\MockServer` and pass in 
the location of your router file.

The router file should contain an instance of the `\EdmondsCommerce\MockServer\StaticRouter` which is a wrapper around
Symfony's router class.

An example of a basic router set up can be found in [router.php](tests/assets/MockServerTest/router.php)
The file should load the Composer autoloader and create the static router before registering routes for different URIs.

The router supports static file routes, callback routes and text routes.

The router also handles dumping request and response data to `tmp` so that it is possible to retrieve what was sent
when running tests via the `MockServer`. 

## Router Types

### Callback

The callback router sets a function which will be called. The return of the function should be text, or a redirect etc.

See [this test](./tests/StaticRouterTest.php#L72) for an exmaple of a callback.

### Text

Second param of `addRuote($uri, $response)` is the text that will be returned after visiting specified uri.

See [this test](./tests/StaticRouterTest.php#L40) for an example of text route.

### Static 

Second param of `addStaticRuote($uri, $response)` is the the file content that will be returned after visiting specified uri.




