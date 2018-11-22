<?php

namespace EdmondsCommerce\MockServer\Routing;

use Closure;
use EdmondsCommerce\MockServer\Exception\RouterException;
use ReflectionFunction;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Route;

class RouteFactory
{
    /**
     * Most basic route, returns a text response
     *
     * @param string $uri
     * @param string $response
     * @param array  $defaults
     *
     * @return Route
     */
    public function textRoute(string $uri, string $response, array $defaults = []): Route
    {
        $options = array_merge(
            ['response' => $response],
            $defaults
        );

        return new Route($uri, $options);
    }


    /**
     * Route that returns a file's content as the response
     * Will attempt to also give the correct mime type of the file
     *
     * @param string      $uri
     * @param string      $filePath
     *
     * @param string|null $contentType
     *
     * @return Route
     * @throws RouterException
     * @throws \ReflectionException
     */
    public function staticRoute(string $uri, string $filePath, string $contentType = null): Route
    {
        if (!file_exists($filePath)) {
            throw new RouterException('Could not find file for static route: ' . $filePath);
        }

        $fileContents = file_get_contents($filePath);
        if ($fileContents === false) {
            throw new RouterException('Could not read file for static route at: ' . realpath($filePath));
        }

        $contentType = $contentType ?? mime_content_type($filePath);

        return $this->callbackRoute($uri,
            function (Request $request) use ($filePath, $fileContents, $contentType): Response {

                $response = new Response($fileContents);
                $response->prepare($request);

                $response->headers->set('Content-Type', (string)$contentType);
                $response->headers->set('Content-Length', (string)filesize($filePath));

                return $response;
            }
        );
    }

    /**
     * @param string  $uri
     * @param Closure $callback
     *
     * @return Route
     * @throws \ReflectionException
     */
    public function callbackRoute(string $uri, Closure $callback): Route
    {
        //TODO Use anonymous class with CallbackInterface to restrict usage
        $returnType = (string)(new ReflectionFunction($callback))->getReturnType();
        if ($returnType !== Response::class) {
            throw new \InvalidArgumentException(
                'invalid return type  "' . $returnType
                . '" - closure must return a "' . Response::class . '" (and type hint for that)'
            );
        }

        return new Route($uri, [
            '_controller' => $callback,
        ]);
    }

    /**
     * @param string $uri
     * @param string $filePath
     *
     * @return Route
     * @throws RouterException
     * @throws \ReflectionException
     */
    public function downloadRoute(string $uri, string $filePath): Route
    {
        if (!file_exists($filePath)) {
            throw new RouterException('File at path ' . $filePath . ' does not exist');
        }

        return $this->callbackRoute(
            $uri,
            function (Request $request) use ($filePath): Response {
                $response = new BinaryFileResponse($filePath);
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    basename($filePath)
                );
                $response->prepare($request);

                return $response;
            }
        );
    }
}