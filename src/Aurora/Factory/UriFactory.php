<?php

namespace AuroraLumina\Factory;

use AuroraLumina\Psr7\Uri;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * UriFactory
 * 
 * Implementation of the UriFactoryInterface that creates instances of UriInterface.
 */
class UriFactory implements UriFactoryInterface
{
    /**
     * Creates a new URI instance from the given URI string.
     *
     * @param string $uri The URI string to parse and create the URI from.
     * @return UriInterface The created URI instance.
     * @throws InvalidArgumentException If the provided URI string is invalid.
     */
    public function createUri(string $uri = ''): UriInterface
    {
        $uriParts = parse_url($uri);

        if ($uriParts === false)
        {
            throw new InvalidArgumentException('Invalid URI provided');
        }

        $scheme     = $uriParts['scheme'] ?? '';
        $user       = $uriParts['user'] ?? '';
        $pass       = $uriParts['pass'] ?? '';
        $host       = $uriParts['host'] ?? '';
        $port       = $uriParts['port'] ?? 80;
        $path       = $uriParts['path'] ?? '';
        $query      = $uriParts['query'] ?? '';
        $fragment   = $uriParts['fragment'] ?? '';

        return new Uri(
            $scheme,
            $host,
            $port,
            $path,
            $query,
            $fragment,
            $user,
            $pass
        );
    }
}
