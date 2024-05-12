<?php

namespace AuroraLumina\Stream;

use Psr\Http\Message\StreamInterface;

class EmptyStream implements StreamInterface
{
    /**
     * Returns an empty string representation of the stream.
     *
     * @return string The empty string
     */
    public function __toString(): string
    {
        return '';
    }

    /**
     * Closes the stream. No operation needed for an empty stream.
     *
     * @return void
     */
    public function close(): void
    {
    }

    /**
     * Detaches the stream. Always returns null for an empty stream.
     *
     * @return null Always returns null
     */
    public function detach()
    {
        return null;
    }

    /**
     * Gets the size of the stream, which is always 0 for an empty stream.
     *
     * @return int|null The size of the stream (always 0 for an empty stream)
     */
    public function getSize(): ?int
    {
        return 0;
    }

    /**
     * Gets the current position of the stream, which is always 0 for an empty stream.
     *
     * @return int The current position of the stream (always 0 for an empty stream)
     */
    public function tell(): int
    {
        return 0;
    }

    /**
     * Checks if the end of the stream has been reached, which is always true for an empty stream.
     *
     * @return bool Always returns true
     */
    public function eof(): bool
    {
        return true;
    }

    /**
     * Checks if the stream is seekable, which is always false for an empty stream.
     *
     * @return bool Always returns false
     */
    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * Seeks to a given offset in the stream. Throws an exception for an empty stream.
     *
     * @param int $offset The offset to seek to
     * @param int $whence Specifies how the cursor position will be calculated
     * @return void
     * @throws \RuntimeException If seeking is attempted on an empty stream
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new \RuntimeException('Cannot seek an empty stream');
    }

    /**
     * Rewinds the stream to the beginning. Throws an exception for an empty stream.
     *
     * @return void
     * @throws \RuntimeException If rewinding is attempted on an empty stream
     */
    public function rewind(): void
    {
        throw new \RuntimeException('Cannot rewind an empty stream');
    }

    /**
     * Checks if the stream is writable, which is always false for an empty stream.
     *
     * @return bool Always returns false
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * Writes data to the stream. Throws an exception for an empty stream.
     *
     * @param string $string The string to write to the stream
     * @return int Number of bytes written (always 0 for an empty stream)
     * @throws \RuntimeException If writing is attempted on an empty stream
     */
    public function write($string): int
    {
        throw new \RuntimeException('Cannot write to an empty stream');
    }

    /**
     * Checks if the stream is readable, which is always false for an empty stream.
     *
     * @return bool Always returns false
     */
    public function isReadable(): bool
    {
        return false;
    }

    /**
     * Reads data from the stream. Always returns an empty string for an empty stream.
     *
     * @param int $length The number of bytes to read
     * @return string The read data (always an empty string for an empty stream)
     */
    public function read($length): string
    {
        return '';
    }

    /**
     * Gets the entire contents of the stream. Always returns an empty string for an empty stream.
     *
     * @return string The contents of the stream (always an empty string for an empty stream)
     */
    public function getContents(): string
    {
        return '';
    }

    /**
     * Gets metadata information about the stream. Always returns an empty array for an empty stream.
     *
     * @param string $key Optional metadata key
     * @return mixed|array|null The metadata information (an empty array for an empty stream)
     */
    public function getMetadata($key = null)
    {
        return $key ? null : [];
    }
}
