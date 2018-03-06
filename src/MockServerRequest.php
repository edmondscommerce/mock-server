<?php declare(strict_types=1);

namespace EdmondsCommerce\MockServer;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class MockServerRequest
 *
 * @package Edmonds\Browser
 * Contains the request parameters that the mock server has received.
 */
class MockServerRequest
{
    /**
     * @var ParameterBag
     */
    private $get;

    /**
     * @var ParameterBag
     */
    private $post;

    /**
     * MockServerRequest constructor.
     *
     * @param string $requestFile
     * @throws \Exception
     */
    public function __construct(string $requestFile)
    {
        if (!is_file($requestFile)) {
            throw new \Exception('Could not find response file to parse');
        }

        $data = file_get_contents($requestFile);

        //Parse the Json file
        $data = json_decode($data, true);
        if ($data === null) {
            throw new \Exception('Could not read the request file: '. $requestFile);
        }

        $this->get = new ParameterBag($data['get'] ?? []);
        $this->post = new ParameterBag($data['post'] ?? []);
    }

    public function getPostVars(): ParameterBag
    {
        return $this->post;
    }

    public function getQueryVars(): ParameterBag
    {
        return $this->get;
    }
}
