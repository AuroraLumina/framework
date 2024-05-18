<?php

namespace AuroraLumina\Factory;

use AuroraLumina\Factory\StreamFactory;
use AuroraLumina\Request\ServerRequest;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

/**
 * Class ServerRequestFactory
 * 
 * A factory class for creating instances of ServerRequestInterface.
 */
class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /** @var UriFactoryInterface The URI factory instance. */
    private UriFactoryInterface $uriFactory;

    /**
     * Constructs a ServerRequestFactory object.
     *
     * @param UriFactoryInterface $uriFactory The URI factory instance.
     */
    public function __construct(UriFactoryInterface $uriFactory)
    {
        $this->uriFactory = $uriFactory;
    }

    /**
     * Create a ServerRequest instance from the global PHP variables.
     *
     * @return ServerRequestInterface The created ServerRequest instance.
     */
    public static function fromGlobals(array $server = null, array $cookie = null, array $get = null, array $files = null): ServerRequestInterface
    {
        $server = $server ?? $_SERVER;
        $cookie = $cookie ?? $_COOKIE;
        $get = $get ?? $_GET;
        $files = $files ?? $_FILES;

        $requestMethod = isset($server['REQUEST_METHOD']) === true ? stripslashes($server['REQUEST_METHOD']) : 'GET';
        $requestUri = isset($server['REQUEST_URI']) === true ? stripslashes($server['REQUEST_URI']) : '/';

        $factory = new self(new UriFactory());
        return $factory->createServerRequest(
            $requestMethod,
            $requestUri,
            $server,
            $cookie,
            $get,
            $files,
            [],
            '1.1'
        );
    }

    /**
     * Create a ServerRequest instance.
     *
     * @param string $method The request method.
     * @param mixed $uri The request URI.
     * @param array $serverParams The server parameters.
     * @param array $cookieParams The cookie parameters.
     * @param array $queryParams The query parameters.
     * @param array $uploadedFiles The uploaded files.
     * @param array $parsedBody The parsed body parameters.
     * @param string $protocolVersion The protocol version.
     * @return ServerRequestInterface The created ServerRequest instance.
     */
    public function createServerRequest(
        string $method,
        $uri,
        array $serverParams = [],
        array $cookieParams = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array $parsedBody = [],
        string $protocolVersion = '1.1'
    ): ServerRequestInterface {
        $uri = $this->uriFactory->createUri($uri);
    
        // Parse headers from the server params
        $headers = [];
        foreach ($serverParams as $name => $value)
        {
            if (strpos($name, 'HTTP_') === 0)
            {
                $name = str_replace('HTTP_', '', $name);
                $name = strtolower(str_replace('_', '-', $name));
                $headers[$name] = [$value];
            }
        }
    
        // Create a stream from php://input
        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream('php://input', 'r');
    
        // Set default attributes
        $attributes = ['_route' => 'default'];
    
        // Create the ServerRequest object
        return new ServerRequest(
            $serverParams,
            $cookieParams,
            $queryParams,
            $uploadedFiles,
            $parsedBody,
            $attributes,
            $method,
            $uri,
            $protocolVersion,
            $headers,
            $body
        );
    }
}
