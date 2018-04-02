<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

use EdmondsCommerce\MockServer\Exception\RouterException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class StaticRouter
 *
 * @package EdmondsCommerce\MockServer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StaticRouter
{
    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * @var \Closure[]
     */
    private $callbacks = [];

    /**
     * @var string
     */
    private $notFoundResponse;

    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

    /**
     * @param string $response
     *
     * @return StaticRouter
     */
    public function setNotFound(string $response): StaticRouter
    {
        $this->notFoundResponse = $response;

        return $this;
    }

    /**
     * @param string $file
     *
     * @return StaticRouter
     * @throws \RunTimeException
     */
    public function setNotFoundStatic(string $file): StaticRouter
    {
        if (!file_exists($file)) {
            throw new \RuntimeException('Could not find 404 file: '.$file);
        }

        return $this->setNotFound(file_get_contents($file));
    }

    /**
     * @param string $uri
     * @param string $fileResponse
     *
     * @return StaticRouter
     * @throws \RuntimeException
     */
    public function addStaticRoute(string $uri, string $fileResponse): StaticRouter
    {
        if (!file_exists($fileResponse)) {
            throw new \RuntimeException('Could not find file '.$fileResponse);
        }

        return $this->addRoute($uri, file_get_contents($fileResponse));
    }

    public function addCallbackRoute(string $uri, string $response, \Closure $closure)
    {
        $this->addRoute($uri, $response);
        $this->callbacks[$uri] = $closure;

        return $this;
    }

    /**
     * @param string $uri
     * @param string $response
     * @param array  $defaults
     *
     * @return StaticRouter
     */
    public function addRoute(string $uri, string $response, array $defaults = []): StaticRouter
    {
        $this->routes->add($uri, new Route($uri, array_merge(['response' => $response], $defaults)));

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param string $requestUri
     *
     * @return array
     * @throws \EdmondsCommerce\MockServer\Exception\RouterException
     */
    public function matchRoute(string $requestUri): array
    {
        $context = new RequestContext();
        $context->fromRequest(Request::createFromGlobals());

        $matcher = new UrlMatcher($this->routes, $context);
        try {
            return $matcher->match($requestUri);
        } catch (ResourceNotFoundException $e) {
            throw new RouterException('Could not find route for '.$requestUri);
        }
    }

    /**
     * @throws \InvalidArgumentException
     * @throws RouterException
     */
    public function respondNotFound(): Response
    {
        if (empty($this->notFoundResponse)) {
            throw new RouterException('No 404 response defined');
        }

        return new Response($this->notFoundResponse, 404);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @param string $requestUri
     *
     * @return Response
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws RouterException
     */
    public function run(string $requestUri = null): Response
    {
        if (empty($requestUri)) {
            $requestUri = $_SERVER['REQUEST_URI'];
        }

        $request = Request::createFromGlobals();
        $this->logRequest($request);

        try {
            $route = $this->matchRoute($requestUri);
        } catch (NoConfigurationException $e) {
            return $this->respondNotFound();
        } catch (RouterException $exception) {
            return $this->respondNotFound();
        }

        //Is there a closure callback registered with this route?
        $callbackResult = '';
        if (isset($this->callbacks[$requestUri])) {
            $callbackResult .= $this->callbacks[$requestUri]();
        }

        $responseBody = $route['response'].$callbackResult;

        //TODO: Need to dump a response object on the file system alongside the request

        return new Response($responseBody);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param Request $request
     *
     * @throws \Exception
     */
    protected function logRequest(Request $request)
    {
        $requestPath = MockServer::getTempDirectory().'/request.json';
        $output      = [
            'post' => $request->request->all(),
            'get'  => $request->query->all(),
        ];

        if (file_put_contents($requestPath, json_encode($output)) === false) {
            throw new \RuntimeException('Could not write request output to '.$requestPath);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param Response $response
     *
     * @throws \Exception
     */
    protected function logResponse(Response $response)
    {
        $responsePath = MockServer::getTempDirectory().'/response.json';
        $output       = $response->getContent();

        if (file_put_contents($responsePath, json_encode($output)) === false) {
            throw new \RuntimeException('Could not write response output to '.$responsePath);
        }
    }
}
