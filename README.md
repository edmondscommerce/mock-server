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
When using the Factory, the configuration for the MockServer is pulled from `MockServerConfig`.
The config in turn checks for values in the `$_SERVER` super global - generally populated with any thing that has been exported from your environment. 

The default values are fairly sensible though. Based upon the project root, the default configuration expects you to have a [`tests`](./tests) folder which in turn contains a [`MockServer`](./tests/MockServer) folder. Inside the MockServer folder we expect a `router.php` file and a `htdocs` folder which contains static assets to be served directly.
 
**Note -** this is exactly as it has been configured in this library.

### Router

An example of a basic router set up can be found in [router.php](./tests/MockServer/router.php)

The router file should contain an instance of the `\EdmondsCommerce\MockServer\Routing\StaticRouter` which is a wrapper around
Symfony's router class. The file should load the Composer autoloader and create the static router before registering routes for different URIs.

The router supports static file routes, callback routes and text routes.
To quickly get started, see the test template under the next heading.

### Starting the server manually
To start the server manually, you can simply use [start-mock-server](./bin/start-mock-server) which will start a backgrounded mock server. If you want it in the foreground, simply pass "foreground" as an argument

```bash
./bin/start-mock-server foreground
```

## Route Types
Routes are created by the `RouteFactory` and registered in the `Router`
There is a public method for each type of route that you can call and pass straight to the `addRoute` method of the router.

### Callback
The callback route sets a closure which will be passed the request object and must return a response object.

### File Download
The download router will return a file as a download. 
Internally it sets a callback that then returns a BinaryFileResponse object

### Static Text

### Static 
Second param of `addStaticRuote($uri, $response)` is the the file content that will be returned after visiting specified uri.
This is useful when you want a file's contents to be used at different locations.

If you are just trying to visit a file directly, using this is not required as this is handled automatically. 




