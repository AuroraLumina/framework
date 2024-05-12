<?php

namespace AuroraLumina\Http\Response;

use AuroraLumina\Stream\Stream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait implementing the various methods defined in MessageInterface.
 *
 * @see https://github.com/php-fig/http-message/tree/master/src/MessageInterface.php
 */
trait MessageTrait
{
    /** @var array<string, array<string>> */
    protected array $headers = [];

    /** @var array<string, string> */
    protected array $headerNames = [];

    /** @var string */
    private string $protocol = '1.1';

    /** @var StreamInterface */
    private StreamInterface $stream;

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     * @throws InvalidArgumentException
     */
    public function withProtocolVersion(string $version): ResponseInterface
    {
        $this->validateProtocolVersion($version);
        $new = clone $this;
        $new->protocol = $version;
        return $new;
    }

    /**
     * Retrieves all message headers.
     *
     * @return array Returns an associative array of the message's headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $header Case-insensitive header name.
     * @return bool Returns true if any header names match the given header name using a case-insensitive string comparison.
     */
    public function hasHeader(string $header): bool
    {
        return isset($this->headerNames[strtolower($header)]);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * @param string $header Case-insensitive header field name.
     * @return array An array of string values as provided for the given header.
     */
    public function getHeader(string $header): array
    {
        if (! $this->hasHeader($header))
        {
            return [];
        }

        $header = $this->headerNames[strtolower($header)];

        return $this->headers[$header];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header.
     */
    public function getHeaderLine(string $name): string
    {
        $value = $this->getHeader($name);
        if (empty($value))
        {
            return '';
        }

        return implode(',', $value);
    }

    /**
     * Return an instance with the provided header, replacing any existing values of any headers with the same case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws InvalidArgumentException For invalid header names or values.
     */
    public function withHeader(string $name, $value): ResponseInterface
    {
        $this->assertHeader($name);

        $normalized = strtolower($name);

        $new = clone $this;
        if ($new->hasHeader($name))
        {
            unset($new->headers[$new->headerNames[$normalized]]);
        }

        $value = $this->filterHeaderValue($value);

        $new->headerNames[$normalized] = $name;
        $new->headers[$name]           = $value;

        return $new;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws InvalidArgumentException For invalid header names or values.
     */
    public function withAddedHeader(string $name, $value): ResponseInterface
    {
        $this->assertHeader($name);

        if (! $this->hasHeader($name)) {
            return $this->withHeader($name, $value);
        }

        $header = $this->headerNames[strtolower($name)];

        $new                   = clone $this;
        $value                 = $this->filterHeaderValue($value);
        $new->headers[$header] = array_merge($this->headers[$header], $value);
        return $new;
    }

    /**
     * Return an instance without the specified header.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return static
     */
    public function withoutHeader(string $name): ResponseInterface
    {
        if ($name === '' || ! $this->hasHeader($name))
        {
            return clone $this;
        }

        $normalized = strtolower($name);
        $original   = $this->headerNames[$normalized];

        $new = clone $this;
        unset($new->headers[$original], $new->headerNames[$normalized]);
        return $new;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody(): StreamInterface
    {
        return $this->stream;
    }

    /**
     * Return an instance with the specified message body.
     *
     * @param StreamInterface $body Body.
     * @return static
     * @throws InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body): ResponseInterface
    {
        $new         = clone $this;
        $new->stream = $body;
        return $new;
    }

    /** @param StreamInterface|string|resource $stream */
    private function getStream($stream, string $modeIfNotInstance): StreamInterface
    {
        if ($stream instanceof StreamInterface)
        {
            return $stream;
        }

        if (! is_string($stream) && ! is_resource($stream))
        {
            throw new \InvalidArgumentException(
                'Stream must be a string stream resource identifier, an actual stream resource, or a Psr\Http\Message\StreamInterface implementation'
            );
        }

        return new Stream($stream, $modeIfNotInstance);
    }

    private function setHeaders(array $originalHeaders): void
    {
        $headerNames = $headers = [];

        foreach ($originalHeaders as $header => $value)
        {
            $value = $this->filterHeaderValue($value);

            $this->assertHeader($header);

            $headerNames[strtolower($header)] = $header;
            $headers[$header]                 = $value;
        }

        $this->headerNames = $headerNames;
        $this->headers     = $headers;
    }

    private function validateProtocolVersion(string $version): void
    {
        if (empty($version))
        {
            throw new \InvalidArgumentException('HTTP protocol version can not be empty');
        }

        // HTTP/1 uses a "<major>.<minor>" numbering scheme to indicate versions of the protocol, while HTTP/2 does not.
        if (! preg_match('#^(1\.[01]|2(\.0)?)$#', $version)) {
            throw new \InvalidArgumentException(sprintf('Unsupported HTTP protocol version "%s" provided', $version));
        }
    }

    /** @return array<int, string> */
    private function filterHeaderValue(mixed $values): array
    {
        if (! is_array($values))
        {
            $values = [$values];
        }

        if ([] === $values)
        {
            throw new \InvalidArgumentException('Invalid header value: must be a string or array of strings; cannot be an empty array');
        }

        return array_map(static function ($value): string
        {
            self::assertValid($value);

            $value = (string) $value;

            // Normalize line folding to a single space (RFC 7230#3.2.4).
            $value = str_replace(["\r\n\t", "\r\n "], ' ', $value);

            // Remove optional whitespace (OWS, RFC 7230#3.2.3) around the header value.
            return trim($value, "\t ");
        }, array_values($values));
    }

    /**
     * Ensure header name and values are valid.
     *
     * @param string $name
     * @throws InvalidArgumentException
     */
    private function assertHeader(string $name): void
    {
        self::assertValidName($name);
    }

    private static function assertValid(mixed $value): void
    {
        if (!is_string($value) && ! is_numeric($value))
        {
            throw new \InvalidArgumentException(sprintf('Invalid header value type; must be a string or numeric; received %s', get_debug_type($value)));
        }
        if (!self::isValid($value))
        {
            throw new \InvalidArgumentException(sprintf('"%s" is not valid header value', $value));
        }
    }

    private function assertValidName(string $name): void
    {
        if (!preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name))
        {
            throw new \InvalidArgumentException(sprintf('Invalid header name "%s"; must be a valid HTTP header name', $name));
        }
    }

    private static function isValid(string $value): bool
    {
        return preg_match("/^[^()<>@,;:\\\"\/\[\]?={} \t\x7f-\xff]*$/", $value);
    }
}

