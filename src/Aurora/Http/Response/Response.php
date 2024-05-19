<?php

namespace AuroraLumina\Http\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

use function sprintf;

/**
 * HTTP response encapsulation.
 *
 * Responses are considered immutable; all methods that might change state are
 * implemented such that they retain the internal state of the current
 * message and return a new instance that contains the changed state.
 */
class Response implements ResponseInterface
{
    use MessageTrait;

    /**
     * Minimum allowed status code value.
     */
    public const MIN_STATUS_CODE_VALUE = 100;

    /**
     * Maximum allowed status code value.
     */
    public const MAX_STATUS_CODE_VALUE = 599;

    /**
     * Map of standard HTTP status code/reason phrases.
     *
     * @psalm-var array<positive-int, non-empty-string>
     */
    private array $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Content Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Content',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended (OBSOLETED)',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    /**
     * HTTP response reason phrase.
     */
    private string $reasonPhrase;

    /**
     * HTTP response status code.
     */
    private int $statusCode;

    /**
     * Constructs a new HTTP response.
     *
     * @param string|resource|StreamInterface $body Stream identifier and/or actual stream resource.
     * @param int $status Status code for the response. Default is 200.
     * @param array $headers Headers for the response, if any.
     */
    public function __construct($body = 'php://memory', int $status = 200, array $headers = [])
    {
        $this->setStatusCode($status);
        $this->stream = $this->getStream($body, 'wb+');
        $this->setHeaders($headers);
    }

    /**
     * Retrieves the HTTP status code of the response.
     *
     * @return int The status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Retrieves the HTTP reason phrase associated with the status code.
     *
     * @return string The reason phrase.
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * Returns a new instance with the specified status code and, optionally, reason phrase.
     *
     * @param int $code The 3-digit HTTP status code to set.
     * @param string $reasonPhrase The reason phrase associated with the status code.
     * @return Response A new instance with the specified status code and reason phrase.
     */
    public function withStatus(int $code, string $reasonPhrase = ''): Response
    {
        $new = clone $this;
        $new->setStatusCode($code, $reasonPhrase);
        return $new;
    }

    /**
     * Sets a valid status code along with its corresponding reason phrase.
     *
     * @param int $code The HTTP status code to set.
     * @param string $reasonPhrase The reason phrase associated with the status code.
     * @throws \InvalidArgumentException If the provided status code is invalid.
     */
    private function setStatusCode(int $code, string $reasonPhrase = ''): void
    {
        if (
            $code < static::MIN_STATUS_CODE_VALUE
            || $code > static::MAX_STATUS_CODE_VALUE
        ) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid status code "%s"; must be an integer between %d and %d, inclusive',
                $code,
                static::MIN_STATUS_CODE_VALUE,
                static::MAX_STATUS_CODE_VALUE
            ));
        }

        if ($reasonPhrase === '' && isset($this->phrases[$code])) {
            $reasonPhrase = $this->phrases[$code];
        }

        $this->reasonPhrase = $reasonPhrase;
        $this->statusCode   = $code;
    }
}
