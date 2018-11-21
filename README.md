# Mock Web Server and Router 
## By [Edmonds Commerce](https://www.edmondscommerce.co.uk)

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a791bb0914a243749b3c9918c70af2da)](https://www.codacy.com/app/edmondscommerce/mock-server?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=edmondscommerce/mock-server&amp;utm_campaign=Badge_Grade) 
[![Build Status](https://travis-ci.org/edmondscommerce/mock-server.svg?branch=master)](https://travis-ci.org/edmondscommerce/mock-server)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/edmondscommerce/mock-server/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/edmondscommerce/mock-server/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/edmondscommerce/mock-server/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/edmondscommerce/mock-server/?branch=master)

## Installation

### PHP 7.0

Install via composer

`composer require edmondscommerce/mock-server:~1 --dev`

### PHP 7.1+

Install via composer

`composer require edmondscommerce/mock-server:~2 --dev`


## Usage

To start the web server, you need to instantiate the `\EdmondsCommerce\MockServer\MockServer` and call `startServer`

```php
<?php
$mockServer=\EdmondsCommerce\MockServer\Factory::getMockServer();
$mockServer->startServer();
```

### Configuration

When using the Factory, the configuration for the MockServer is pulled from `MockServerConfig` which in turn checks for values in the `$_SERVER` superglobal - generally populated with any thing that has been exported from your environment. 

The default values are fairly sensible though. Based upon the project root, the default configuration expects you to have a [`tests`](./tests) folder which in turn contains a [`MockServer`](./tests/MockServer) folder. Inside the MockServer folder we expect a `router.php` file and a `htdocs` folder which contains static assets to be served directly.
 
**Note -** this is exactly as it has been configured in this library.

### Router

An example of a basic router set up can be found in [router.php](./tests/MockServer/router.php)

The router file should contain an instance of the `\EdmondsCommerce\MockServer\Routing\StaticRouter` which is a wrapper around
Symfony's router class. The file should load the Composer autoloader and create the static router before registering routes for different URIs.

The router supports static file routes, callback routes and text routes.

**Note -** there are helpful includes [routerTop.php](./src/include/routerTop.php) and [routerBottom.php](./src/include/routerBottom.php) which handle some basic boilerplate for you.

#### Template

Here is a template router for you to start with:

```php
<?php declare(strict_types=1);
require __DIR__.'/../../vendor/edmondscommerce/mock-server/src/include/routerTop.php';

$router = \EdmondsCommerce\MockServer\Factory::getStaticRouter();

//Add your routes here

/**
 * IMPORTANT - you have to `return` the required routerBottom
 */
return require __DIR__.'/../../vendor/edmondscommerce/mock-server/src/include/routerBottom.php';
```

### Starting the server manually

To start the server manually, you can simply use [start-mock-server](./bin/start-mock-server) which will start a backgrounded mock server. If you want it in the foreground, simply pass "foreground" as an argument

```bash
./bin/start-mock-server foreground
```

If you want the server to listen on a specific IP address, you can do this by exporting a config variable:

```bash
export MockServer_Ip="0.0.0.0"
```


## Router Types

### Static Files (css/js/html/etc...)

Static files that are located in the htdocs folder will be served without any further configuration

For the full list of support file types, see: [StaticRouter::STATIC_EXTENSIONS_SUPPORTED](./src/StaticRouter.php#L27)

### Callback

The callback router sets a closure which will be passed the request object and must return a response object.

See [this test](./tests/StaticRouterTest.php#L88) for an exmaple of a callback.

### File Download

The download router will return a file as a download. Internally it sets a callback that then returns a BinaryFileResponse object

### Static Text

Second param of `addRoute($uri, $response)` is the text that will be returned after visiting specified uri.

See [this test](./tests/StaticRouterTest.php#L40) for an example of text route.

### Static 

Second param of `addStaticRuote($uri, $response)` is the the file content that will be returned after visiting specified uri.

Do not use this to return files that are in the htdocs folder, it's pointless. This is largely being kept for legacy reasons.




