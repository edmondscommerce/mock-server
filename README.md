# Mock Web Server and Router 
## By [Edmonds Commerce](https://www.edmondscommerce.co.uk)

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
