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
    public const NOT_FOUND = 'Not Found';

    public const STATIC_EXTENSIONS_SUPPORTED = [
        '3gp',
        'apk',
        'avi',
        'bmp',
        'css',
        'csv',
        'doc',
        'docx',
        'flac',
        'gif',
        'gz',
        'gzip',
        'htm',
        'html',
        'ics',
        'jpe',
        'jpeg',
        'jpg',
        'js',
        'kml',
        'kmz',
        'm4a',
        'mov',
        'mp3',
        'mp4',
        'mpeg',
        'mpg',
        'odp',
        'ods',
        'odt',
        'oga',
        'ogg',
        'ogv',
        'pdf',
        'pdf',
        'png',
        'pps',
        'pptx',
        'qt',
        'svg',
        'swf',
        'tar',
        'text',
        'tif',
        'txt',
        'wav',
        'webm',
        'wmv',
        'xls',
        'xlsx',
        'xml',
        'xsl',
        'xsd',
        'zip',
    ];

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

    /**
     * @var string
     */
    private $htdocsPath;

    private $verbose = false;

    public function __construct(string $htdocsPath)
    {
        $this->routes = new RouteCollection();
        $this->setNotFound(static::NOT_FOUND);
        $this->htdocsPath = $htdocsPath;
        if (!is_dir($this->htdocsPath)) {
            throw new \RuntimeException('htdocs path does not exist: '.$this->htdocsPath);
        }
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
    public function run(string $requestUri = null): ?Response
    {
        if (null !== $requestUri) {
            $_SERVER['REQUEST_URI'] = $requestUri;
        }
        if ('/' === $_SERVER['REQUEST_URI']) {
            $_SERVER['REQUEST_URI'] = '/index.html';
        }
        $request = Request::createFromGlobals();
        $this->logRequest($request);
        if ($this->isStaticAsset($request)) {
            return null;
        }
        $response = $this->getResponse($request);
        $this->logResponse($response);

        return $response;
    }

    /**
     * @param Request $request
     *
     * Is the request for a static file that exists in the htdocs folder and has a supported extension?
     *
     * @return bool
     */
    public function isStaticAsset(Request $request): bool
    {
        $uri = $request->getRequestUri();
        if (file_exists($this->htdocsPath.'/'.$uri)
            && \in_array(
                \pathinfo($uri, PATHINFO_EXTENSION),
                self::STATIC_EXTENSIONS_SUPPORTED,
                true
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \InvalidArgumentException
     * @throws RouterException
     */
    protected function getResponse(Request $request): Response
    {
        try {
            $route = $this->matchRoute($request->getRequestUri());
        } catch (NoConfigurationException $e) {
            return $this->respondNotFound();
        } catch (RouterException $exception) {
            return $this->respondNotFound();
        }

        //Is there a closure callback registered with this route?
        $callbackResult = '';
        if (isset($this->callbacks[$request->getRequestUri()])) {
            $callbackResult .= $this->callbacks[$request->getRequestUri()]();
        }

        $responseBody = (string)$route['response'].$callbackResult;

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
        $requestPath = MockServer::getLogsPath().'/'.MockServer::REQUEST_FILE;
        $output      = [
            'post'   => $request->request->all(),
            'get'    => $request->query->all(),
            'server' => $request->server->all(),
            'files'  => $request->files->all(),
        ];
        $uri         = $request->getRequestUri();
        if (true === $this->verbose) {
            file_put_contents('php://stderr', "\nRequest: $uri\n".var_export($output, true));
        }

        if (file_put_contents($requestPath, serialize($request)) === false) {
            throw new \RuntimeException('Could not write request output to '.$requestPath);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param Response $response
     *
     * @throws \Exception
     */
    protected function logResponse(Response $response): void
    {
        $responsePath = MockServer::getLogsPath().'/'.MockServer::RESPONSE_FILE;

        $log = [
            'headers' => $response->headers->all(),
            'output'  => $response->getContent(),
        ];
        if (true === $this->verbose) {
            file_put_contents('php://stderr', "\nResponse: \n".var_export($log, true));
        }

        if (file_put_contents($responsePath, serialize($response)) === false) {
            throw new \RuntimeException('Could not write response output to '.$responsePath);
        }
    }

    /**
     * @param bool $verbose
     *
     * @return StaticRouter
     */
    public function setVerbose(bool $verbose): StaticRouter
    {
        $this->verbose = $verbose;

        return $this;
    }
}
