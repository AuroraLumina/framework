<?php

namespace AuroraLumina\Http\Response;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use AuroraLumina\Stream\EmptyStream;

class EmptyResponse implements ResponseInterface
{
    /**
     * The HTTP status code.
     *
     * @var int
     */
    private int $statusCode = 200;

    /**
     * The reason phrase associated with the status code.
     *
     * @var string
     */
    private string $reasonPhrase = '';

    /**
     * The HTTP headers.
     *
     * @var array
     */
    private array $headers = [];

    /**
     * The response body stream.
     *
     * @var StreamInterface|null
     */
    private ?StreamInterface $body = null;

    /**
     * The HTTP protocol version.
     *
     * @var string
     */
    private string $protocol = '1.1';

    /**
     * Constructs a new EmptyResponse instance.
     *
     * @param int $status The HTTP status code
     * @param array $headers The HTTP headers
     */
    public function __construct(int $status = 200, array $headers = [])
    {
        $this->statusCode = $status;
        $this->headers = $headers;
        $this->body = new EmptyStream();
    }

    /**
     * Gets the HTTP status code of the response.
     *
     * @return int The HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Returns a new instance with the specified HTTP status code and reason phrase.
     *
     * @param int $code The HTTP status code
     * @param string $reasonPhrase The reason phrase
     * @return ResponseInterface A new instance with the specified status code and reason phrase
     */
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $this->statusCode = $code;
        $this->reasonPhrase = $reasonPhrase;
        return $this;
    }

    /**
     * Gets the reason phrase associated with the status code.
     *
     * @return string The reason phrase
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * Gets the HTTP protocol version.
     *
     * @return string The HTTP protocol version
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * Returns a new instance with the specified HTTP protocol version.
     *
     * @param string $version The HTTP protocol version
     * @return ResponseInterface A new instance with the specified protocol version
     */
    public function withProtocolVersion($version): ResponseInterface
    {
        $this->protocol = $version;
        return $this;
    }

    /**
     * Gets the HTTP headers of the response.
     *
     * @return array The HTTP headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Checks if a header exists.
     *
     * @param string $name The header name
     * @return bool True if the header exists, false otherwise
     */
    public function hasHeader($name): bool
    {
        return isset($this->headers[$name]);
    }

    /**
     * Gets a header value.
     *
     * @param string $name The header name
     * @return array The header value(s)
     */
    public function getHeader($name): array
    {
        return $this->hasHeader($name) ? [$this->headers[$name]] : [];
    }

    /**
     * Gets a header line.
     *
     * @param string $name The header name
     * @return string The header line
     */
    public function getHeaderLine($name): string
    {
        return $this->hasHeader($name) ? $this->headers[$name] : '';
    }

    /**
     * Returns a new instance with the specified header.
     *
     * @param string $name The header name
     * @param mixed $value The header value
     * @return ResponseInterface A new instance with the specified header
     */
    public function withHeader($name, $value): ResponseInterface
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Returns a new instance with the specified added header.
     *
     * @param string $name The header name
     * @param mixed $value The header value
     * @return ResponseInterface A new instance with the specified added header
     */
    public function withAddedHeader($name, $value): ResponseInterface
    {
        if ($this->hasHeader($name)) {
            $this->headers[$name] .= ', ' . $value;
        } else {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    /**
     * Returns a new instance without the specified header.
     *
     * @param string $name The header name
     * @return ResponseInterface A new instance without the specified header
     */
    public function withoutHeader($name): ResponseInterface
    {
        unset($this->headers[$name]);
        return $this;
    }

    /**
     * Gets the body of the response.
     *
     * @return StreamInterface The response body
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * Returns a new instance with the specified body.
     *
     * @param StreamInterface $body The response body
     * @return ResponseInterface A new instance with the specified body
     */
    public function withBody(StreamInterface $body): ResponseInterface
    {
        $this->body = $body;
        return $this;
    }
}
