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

    private const ALLOWED_FORM_CONTENT_TYPES = [
        'application/x-www-form-urlencoded',
        'multipart/form-data',
    ];

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
     * Checks if the provided content type indicates a form submission.
     *
     * @param string $contentType The content type to check.
     * @return bool True if the content type indicates a form submission, false otherwise.
     */
    private function isFormContentType($contentType): bool
    {
        foreach (self::ALLOWED_FORM_CONTENT_TYPES as $allowedType) {
            if (strpos($contentType, $allowedType) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a ServerRequest instance from the global PHP variables.
     *
     * @return ServerRequest The created ServerRequest instance.
     */
    public static function fromGlobals(): ServerRequest
    {
        $requestMethod = isset($_SERVER['REQUEST_METHOD']) === true ? stripslashes($_SERVER['REQUEST_METHOD']) : 'GET';
        $requestUri = isset($_SERVER['REQUEST_URI']) === true ? stripslashes($_SERVER['REQUEST_URI']) : '/';

        $factory = new self(new UriFactory());
        return $factory->createServerRequest(
            $requestMethod,
            $requestUri,
            $_SERVER,
            $_COOKIE,
            [],
            [],
            $_FILES,
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
        mixed $uri,
        array $serverParams = [],
        array $cookieParams = [],
        array $queryParams = [],
        array $postParams = [],
        array $uploadedFiles = [],
        array $parsedBody = [],
        string $protocolVersion = '1.1'
    ): ServerRequest {
        $uri = $this->uriFactory->createUri($uri);
        
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
        
        $queryParams = array_merge($queryParams, $_GET);
        if ($method === 'POST' && $this->isFormContentType($serverParams['CONTENT_TYPE']))
        {
            $postParams = array_merge($postParams, $_POST);
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
            $postParams,
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
