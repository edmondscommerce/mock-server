<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer\Routing;

use EdmondsCommerce\MockServer\Exception\MockServerException;
use EdmondsCommerce\MockServer\Exception\RouterException;
use EdmondsCommerce\MockServer\MockServer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
        'ico',
        'ics',
        'jpe',
        'jpeg',
        'jpg',
        'js',
        'json',
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
     * @var string
     */
    private $notFoundResponse = self::NOT_FOUND;

    /**
     * @var string
     */
    private $htdocsPath;

    /**
     * @var bool
     */
    private $verbose = false;

    /**
     * StaticRouter constructor.
     *
     * @param string          $publicDir
     * @param RouteCollection $routes
     *
     * @throws MockServerException
     */
    public function __construct(string $publicDir, RouteCollection $routes)
    {
        $this->routes     = $routes;
        $this->htdocsPath = $publicDir;

        $this->checkPublicDir();
    }

    /**
     * @throws MockServerException
     */
    private function checkPublicDir(): void
    {
        if (!is_dir($this->htdocsPath)) {
            throw new MockServerException('htdocs path does not exist: ' . $this->htdocsPath);
        }
    }

    /**
     * @param Route $route
     *
     * @return StaticRouter
     */
    public function addRoute(Route $route): StaticRouter
    {
        //TODO: Prevent route name collision
        $this->routes->add($route->getPath(), $route);

        return $this;
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
     * @throws MockServerException
     */
    public function setNotFoundStatic(string $file): StaticRouter
    {
        if (!file_exists($file)) {
            throw new MockServerException('Could not find 404 file: ' . $file);
        }

        $fileContents = file_get_contents($file);
        if ($fileContents === false) {
            throw new MockServerException('Could not read 404 file at: ' . $file);
        }

        return $this->setNotFound($fileContents);
    }

    /**
     * @param string      $uri
     * @param string      $fileResponse
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Complete_list_of_MIME_types
     *
     * @param null|string $contentType
     *
     * @return StaticRouter
     * @throws \Exception
     */
    public function addStaticRoute(string $uri, string $fileResponse, ?string $contentType = null): StaticRouter
    {
        if (!file_exists($fileResponse)) {
            throw new \RuntimeException('Could not find file ' . $fileResponse);
        }
        $contentType = $contentType ?? mime_content_type($fileResponse);
        $this->addCallbackRoute($uri,
            function (Request $request) use ($fileResponse, $contentType): Response {

                $response = new Response(file_get_contents($fileResponse));
                $response->prepare($request);
                $response->headers->set('Content-Type', (string)$contentType);
                $response->headers->set('Content-Length', (string)filesize($fileResponse));

                return $response;
            }
        );

        return $this;
    }

    /**
     * @param string   $uri
     * @param \Closure $closure - must return a Response object
     *
     * @return $this
     * @throws \Exception
     */
    public function addCallbackRoute(string $uri, \Closure $closure)
    {
        $returnType = (string)(new \ReflectionFunction($closure))->getReturnType();
        if ($returnType !== Response::class) {
            throw new \InvalidArgumentException(
                'invalid return type  "' . $returnType
                . '" - closure must return a "' . Response::class . '" (and type hint for that)'
            );
        }
        $this->routes->add(
            $uri,
            new Route(
                $uri,
                ['_controller' => $closure]
            )
        );

        return $this;
    }

    /**
     * @param string $uri
     * @param string $pathToFile
     *
     * @return StaticRouter
     * @throws \Exception
     */
    public function addFileDownloadRoute(string $uri, string $pathToFile): StaticRouter
    {
        $this->addCallbackRoute(
            $uri,
            function (Request $request) use ($pathToFile): Response {
                $response = new BinaryFileResponse($pathToFile);
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    basename($pathToFile)
                );
                $response->prepare($request);

                return $response;
            }
        );

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param Request $request
     *
     * @return array
     * @throws \Symfony\Component\Routing\Exception\MethodNotAllowedException
     * @throws RouterException
     */
    public function matchRoute(Request $request): array
    {
        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->routes, $context);
        try {
            return $matcher->match($request->getRequestUri());
        } catch (ResourceNotFoundException $e) {
            throw new RouterException('Could not find route for ' . $request->getRequestUri());
        }
    }

    /**
     * @throws \InvalidArgumentException
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
        return $this->getResponse($request);
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
        if (file_exists($this->htdocsPath . '/' . $uri)
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
     */
    protected function getResponse(Request $request): Response
    {
        try {
            $route = $this->matchRoute($request);
        } catch (NoConfigurationException $e) {
            return $this->respondNotFound();
        } catch (RouterException $exception) {
            return $this->respondNotFound();
        }

        /**
         * The _controller is our callback closure
         */
        if (isset($route['_controller'])) {
            return $route['_controller']($request);
        }

        return new Response($route['response']);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param Request $request
     *
     * @throws \Exception
     */
    protected function logRequest(Request $request): void
    {
        $requestPath = MockServer::getLogsPath() . '/' . MockServer::REQUEST_FILE;
        $output      = [
            'post'   => $request->request->all(),
            'get'    => $request->query->all(),
            'server' => $request->server->all(),
            'files'  => $request->files->all(),
        ];
        $uri         = $request->getRequestUri();
        if (true === $this->verbose) {
            file_put_contents('php://stderr', "\nRequest: $uri\n" . var_export($output, true));
        }

        if (file_put_contents($requestPath, serialize($request)) === false) {
            throw new \RuntimeException('Could not write request output to ' . $requestPath);
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
