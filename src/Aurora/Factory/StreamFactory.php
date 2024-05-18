<?php

namespace AuroraLumina\Factory;

use Psr\Http\Message\StreamInterface;

/**
 * StreamFactory
 * 
 * A factory class for creating instances of StreamInterface.
 */
class StreamFactory
{
    /**
     * Create a new stream instance.
     *
     * @param string $content The initial content of the stream.
     * @param string $mode The mode for opening the stream.
     * @return StreamInterface The created stream instance.
     * @throws \RuntimeException If unable to create the stream.
     */
    public function createStream(string $content = '', string $mode = 'r'): StreamInterface
    {
        if (function_exists('fopen'))
        {
            $resource = @fopen('php://memory', $mode);
            if ($resource === false)
            {
                throw new \RuntimeException('Unable to create stream from php://memory');
            }
            
            fwrite($resource, $content);
            rewind($resource);
            
            return new class($resource) implements StreamInterface
            {
                private $resource;

                public function __construct($resource)
                {
                    $this->resource = $resource;
                }
                
                public function __destruct()
                {
                    if (is_resource($this->resource) === true) {
                        fclose($this->resource);
                    }
                }
                
                public function close(): void
                {
                    if (is_resource($this->resource) === true) {
                        fclose($this->resource);
                    }
                }
                
                public function detach()
                {
                    return $this->resource;
                }
                
                public function getSize(): int
                {
                    return fstat($this->resource)['size'];
                }
                
                public function tell(): int
                {
                    return ftell($this->resource);
                }
                
                public function eof(): bool
                {
                    return feof($this->resource);
                }
                
                public function isSeekable(): bool
                {
                    $metaData = stream_get_meta_data($this->resource);
                    $isSeekable = isset($metaData['seekable']) && $metaData['seekable'];
                    return $isSeekable;
                }
                
                public function seek(int $offset, int $whence = SEEK_SET): void
                {
                    fseek($this->resource, $offset, $whence);
                }
                
                public function rewind(): void
                {
                    rewind($this->resource);
                }
                
                public function isReadable(): bool
                {
                    return is_readable($this->resource);
                }

                public function read(int $length): string
                {
                    return fread($this->resource, $length);
                }

                public function isWritable(): bool
                {
                    return is_writable($this->resource);
                }

                public function write(string $string): int
                {
                    return fwrite($this->resource, $string);
                }

                public function getContents(): string
                {
                    return stream_get_contents($this->resource);
                }
                
                /**
                 * Retrieves metadata associated with the stream resource.
                 *
                 * @param string|null $key The specific metadata key to retrieve. If null, retrieves all metadata.
                 *
                 * @return mixed|null The requested metadata value if the key is provided and exists, otherwise null.
                 */
                public function getMetadata(string|null $key = null): mixed
                {
                    if ($key === null) {
                        return $this->resource;
                    }
                    else if (isset($this->resource[$key]))
                    {
                        return $this->resource[$key];
                    }
                    else
                    {
                        return null;
                    }
                }

                public function __toString(): string
                {
                    return $this->getContents();
                }
            };
        }
        throw new \RuntimeException('Unable to create stream from php://memory');
    }
}
