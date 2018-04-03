# Mock Web Server and Router 
## By [Edmonds Commerce](https://www.edmondscommerce.co.uk)

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a791bb0914a243749b3c9918c70af2da)](https://www.codacy.com/app/edmondscommerce/mock-server?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=edmondscommerce/mock-server&amp;utm_campaign=Badge_Grade) [![Build Status](https://travis-ci.org/edmondscommerce/mock-server.svg?branch=master)](https://travis-ci.org/edmondscommerce/mock-server)

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

The router file should contain an instance of the `\EdmondsCommerce\MockServer\StaticRouter` which is a wrapper around
Symfony's router class. The file should load the Composer autoloader and create the static router before registering routes for different URIs.

The router supports static file routes, callback routes and text routes.

**Note -** there are helpful includes [routerTop.php](./src/include/routerTop.php) and [routerBottom.php](./src/include/routerBottom.php) which handle some basic boilerplate for you.

### Starting the server manually

To start the server manually, you can simply use [`./bin/start-mock-server`](./bin/start-mock-server) which will start a backgrounded mock server. If you want it in the foreground, simply pass "foreground" as an argument

```bash
./bin/start-mock-server foreground

```

## Router Types

### Static Files (css/js/etc...)

Static files that are located in the htdocs folder will be served without any further configuration

### Callback

The callback router sets a function which will be called. The return of the function should be text, or a redirect etc.

See [this test](./tests/StaticRouterTest.php#L75) for an exmaple of a callback.

### Text

Second param of `addRoute($uri, $response)` is the text that will be returned after visiting specified uri.

See [this test](./tests/StaticRouterTest.php#L40) for an example of text route.

### Static 

Second param of `addStaticRuote($uri, $response)` is the the file content that will be returned after visiting specified uri.




