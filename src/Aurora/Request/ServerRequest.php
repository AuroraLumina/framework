<?php

namespace AuroraLumina\Request;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ServerRequest implementation
 */
class ServerRequest implements ServerRequestInterface
{
    /**
     * @var array Server parameters
     */
    private array $serverParams;

    /**
     * @var array Cookie parameters
     */
    private array $cookieParams = [];

    /**
     * @var array Query parameters
     */
    private array $queryParams = [];

    /**
     * @var array Uploaded files
     */
    private array $uploadedFiles = [];

    /**
     * @var array Parsed body
     */
    private array $parsedBody;

    /**
     * @var array Attributes
     */
    private array $attributes = [];

    /**
     * @var string Request method
     */
    private string $method;

    /**
     * @var UriInterface Request URI
     */
    private UriInterface $uri;

    /**
     * @var string Protocol version
     */
    private string $protocolVersion;

    /**
     * @var array Request headers
     */
    private array $headers;

    /**
     * @var StreamInterface Request body
     */
    private StreamInterface $body;

    /**
     * @var string Request target
     */
    private string $requestTarget;

    /**
     * Constructor
     *
     * @param array $serverParams Server parameters
     * @param array $cookieParams Cookie parameters
     * @param array $queryParams Query parameters
     * @param array $uploadedFiles Uploaded files
     * @param array $parsedBody Parsed body
     * @param array $attributes Attributes
     * @param string $method Request method
     * @param UriInterface $uri Request URI
     * @param string $protocolVersion Protocol version
     * @param array $headers Request headers
     * @param StreamInterface $body Request body
     * @param string $requestTarget Request target (optional, default '/')
     */
    public function __construct(
        array $serverParams,
        array $cookieParams,
        array $queryParams,
        array $uploadedFiles,
        array $parsedBody,
        array $attributes,
        string $method,
        UriInterface $uri,
        string $protocolVersion,
        array $headers,
        StreamInterface $body,
        string $requestTarget = '/'
    ) {
        $this->serverParams = $serverParams;
        $this->cookieParams = $cookieParams;
        $this->queryParams = $queryParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->parsedBody = $parsedBody;
        $this->attributes = $attributes;
        $this->method = $method;
        $this->uri = $uri;
        $this->protocolVersion = $protocolVersion;
        $this->headers = $headers;
        $this->body = $body;
        $this->requestTarget = $requestTarget;
    }

    /**
     * Get server parameters
     *
     * @return array
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * Get cookie parameters
     *
     * @return array
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * Return a new instance with the specified cookie parameters
     *
     * @param array $cookies Cookie parameters
     * @return ServerRequestInterface
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    /**
     * Get query parameters
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * Return a new instance with the specified query parameters
     *
     * @param array $query Query parameters
     * @return ServerRequestInterface
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    /**
     * Get uploaded files
     *
     * @return array
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * Return a new instance with the specified uploaded files
     *
     * @param array $uploadedFiles Uploaded files
     * @return ServerRequestInterface
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    /**
     * Get parsed body parameters
     *
     * @return array|object|null
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Return a new instance with the specified parsed body parameters
     *
     * @param array|object|null $data Parsed body data
     * @return ServerRequestInterface
     */
    public function withParsedBody($data): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->parsedBody = $data;
        return $clone;
    }

    /**
     * Get attributes
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get a single attribute
     *
     * @param string $name Attribute name
     * @param mixed $default Default value (optional, default null)
     * @return mixed
     */
    public function getAttribute(string $name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * Return a new instance with the specified attribute
     *
     * @param string $name Attribute name
     * @param mixed $value Attribute value
     * @return ServerRequestInterface
     */
    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    /**
     * Return a new instance that removes the specified attribute
     *
     * @param string $name Attribute name
     * @return ServerRequestInterface
     */
    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }

    /**
     * Get request method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Return a new instance with the specified request method
     *
     * @param string $method Request method
     * @return ServerRequestInterface
     */
    public function withMethod(string $method): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->method = $method;
        return $clone;
    }

    /**
     * Get request URI
     *
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * Return a new instance with the specified request URI
     *
     * @param UriInterface $uri Request URI
     * @return ServerRequestInterface
     */
    public function withUri(UriInterface $uri, $preserveHost = false): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost) {
            if ($host = $uri->getHost()) {
                $clone->headers['host'] = [$host];
            }
        }

        return $clone;
    }

    /**
     * Get protocol version
     *
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * Return a new instance with the specified protocol version
     *
     * @param string $version Protocol version
     * @return ServerRequestInterface
     */
    public function withProtocolVersion(string $version): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->protocolVersion = $version;
        return $clone;
    }

    /**
     * Get headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Check if a header exists
     *
     * @param string $name Header name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * Get a single header
     *
     * @param string $name Header name
     * @return array
     */
    public function getHeader(string $name): array
    {
        return $this->headers[strtolower($name)]?? [];
    }

    /**
     * Get a single header as a string
     *
     * @param string $name Header name
     * @return string
     */
    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * Return a new instance with the specified header
     *
     * @param string $name Header name
     * @param string|string[] $value Header value
     * @return ServerRequestInterface
     */
    public function withHeader(string $name, $value): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->headers[strtolower($name)] = is_array($value)? $value : [$value];
        return $clone;
    }

    /**
     * Return a new instance with the specified header added
     *
     * @param string $name Header name
     * @param string|string[] $value Header value
     * @return ServerRequestInterface
     */
    public function withAddedHeader(string $name, $value): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->headers[strtolower($name)] = array_merge($this->getHeader($name), is_array($value)? $value : [$value]);
        return $clone;
    }

    /**
     * Return a new instance without the specified header
     *
     * @param string $name Header name
     * @return ServerRequestInterface
     */
    public function withoutHeader(string $name): ServerRequestInterface
    {
        $clone = clone $this;
        unset($clone->headers[strtolower($name)]);
        return $clone;
    }

    /**
     * Get request body
     *
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * Return a new instance with the specified request body
     *
     * @param StreamInterface $body Request body
     * @return ServerRequestInterface
     */
    public function withBody(StreamInterface $body): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    /**
     * Get request target
     *
     * @return string
     */
    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }

    /**
     * Return a new instance with the specified request target
     *
     * @param string $requestTarget Request target
     * @return ServerRequestInterface
     */
    public function withRequestTarget(string $requestTarget): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
    }
}