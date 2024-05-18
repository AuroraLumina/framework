<?php

namespace AuroraLumina\Stream;

use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Throwable;

/**
 * Implementation of PSR HTTP streams
 */
class Stream implements StreamInterface
{
    /** @var array List of allowed stream resource types */
    private const ALLOWED_STREAM_RESOURCE_TYPES = ['stream'];

    /** @var resource|null Stream resource */
    protected $resource;

    /** @var string|object|resource|null Stream reference */
    protected $stream;

    /**
     * Constructor.
     *
     * @param string|object|resource $stream
     * @param string $mode
     * @throws RuntimeException
     */
    public function __construct($stream, string $mode = 'r')
    {
        $this->setStream($stream, $mode);
    }

    /**
     * Converts the stream to a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        if (!$this->isReadable()) {
            return '';
        }

        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }

            return $this->getContents();
        } catch (RuntimeException) {
            return '';
        }
    }

    /**
     * Closes the stream and any underlying resources.
     */
    public function close(): void
    {
        if (!$this->resource) {
            return;
        }

        $resource = $this->detach();
        fclose($resource);
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * @return mixed
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    /**
     * Attach a new stream/resource to the instance.
     *
     * @param string|object|resource $resource
     * @param string $mode
     * @throws RuntimeException
     */
    public function attach($resource, string $mode = 'r'): void
    {
        $this->setStream($resource, $mode);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        if (null === $this->resource) {
            return null;
        }

        $stats = fstat($this->resource);
        return $stats !== false ? $stats['size'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        if (!$this->resource) {
            throw new RuntimeException('No resource available; cannot seek position');
        }

        $result = ftell($this->resource);
        if (!is_int($result)) {
            throw new RuntimeException('Error seeking within stream');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        if (!$this->resource) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        if (!$this->resource) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        return $meta['seekable'];
    }

    /**
     * {@inheritdoc}
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->resource) {
            throw new RuntimeException('No resource available; cannot seek position');
        }

        if (!$this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable');
        }

        $result = fseek($this->resource, $offset, $whence);

        if (0 !== $result) {
            throw new RuntimeException('Error seeking within stream');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        if (!$this->resource) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];

        return strpbrk($mode, 'xwca+') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        if (!$this->resource) {
            throw new RuntimeException('No resource available; cannot seek position');
        }

        if (!$this->isWritable()) {
            throw new RuntimeException('Stream is not writable');
        }

        $result = fwrite($this->resource, $string);

        if ($result === false) {
            throw new RuntimeException('Error writing to stream');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        if (!$this->resource) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];

        return strpbrk($mode, 'r+') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $length): string
    {
        if (!$this->resource) {
            throw new RuntimeException('Stream is not readable');
        }

        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }

        $result = stream_get_contents($this->resource, $length);

        if ($result === false) {
            throw new RuntimeException('Error reading stream');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }

        $contents = stream_get_contents($this->resource);
        if ($contents === false) {
            throw new RuntimeException('Error reading stream contents');
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata(?string $key = null)
    {
        // Get the metadata of the stream
        $metadata = stream_get_meta_data($this->resource);

        // If no specific key is provided, return all metadata
        if ($key === null) {
            // Sanitize the metadata to remove any potentially sensitive information
            return $this->sanitizeMetadata($metadata);
        }

        // Return the value corresponding to the provided key, if it exists
        return isset($metadata[$key]) ? $metadata[$key] : null;
    }

    // Sanitize metadata to remove sensitive information
    private function sanitizeMetadata(array $metadata): array
    {
        // Define keys to exclude from the sanitized metadata
        $excludedKeys = ['uri', 'wrapper_data'];

        // Loop through metadata and remove excluded keys
        foreach ($excludedKeys as $key) {
            if (isset($metadata[$key])) {
                unset($metadata[$key]);
            }
        }

        return $metadata;
    }

    /**
     * Set the internal stream resource.
     *
     * @param string|object|resource $stream String stream target or stream resource.
     * @param string $mode Resource mode for stream target.
     * @throws RuntimeException For invalid streams or resources.
     */
    private function setStream($stream, string $mode = 'r'): void
    {
        $resource = $stream;

        if (is_string($stream)) {
            try {
                $resource = @fopen($stream, $mode);
            }
            catch (Throwable $error)
            {
                throw new RuntimeException(
                    sprintf('Invalid stream reference provided: %s', htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8')),
                    0,
                    $error
                );
            }
        }

        if (!is_resource($resource) || !in_array(get_resource_type($resource), self::ALLOWED_STREAM_RESOURCE_TYPES, true)) {
            throw new RuntimeException('Invalid stream provided; must be a string stream identifier or stream resource');
        }

        $this->resource = $resource;
        $this->stream = $stream;
    }
}
