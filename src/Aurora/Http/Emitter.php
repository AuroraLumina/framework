<?php

namespace AuroraLumina\Http;

use Psr\Http\Message\ResponseInterface;

class Emitter
{
    /**
     * Emit an HTTP response to the client.
     *
     * @param ResponseInterface $response The HTTP response to emit.
     * @param bool $cleanDebuff Clear output
     * @return void
     */
    public function emit(ResponseInterface $response, bool $cleanDebuff = true): void
    {
        if ($cleanDebuff)
        {
            $this->cleanOutputBuffer();
        }

        $this->sendHeaders($response);

        $this->sendBody($response);
    }

    /**
     * Clean the output buffer, if necessary.
     *
     * @return void
     */
    private function cleanOutputBuffer(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    /**
     * Send the headers of the HTTP response.
     *
     * @param ResponseInterface $response The HTTP response.
     * @return void
     */
    private function sendHeaders(ResponseInterface $response): void
    {
        ob_start();

        $statusCode = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();
        $protocol = $response->getProtocolVersion();

        header("HTTP/{$protocol} {$statusCode} {$reasonPhrase}", true, $statusCode);

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("{$name}: {$value}", false);
            }
        }

        foreach ($response->getHeader('Set-Cookie') as $value) {
            header("Set-Cookie: {$value}", false);
        }

        ob_end_flush();
    }

    /**
     * Send the body of the HTTP response.
     *
     * @param ResponseInterface $response The HTTP response.
     * @return void
     */
    private function sendBody(ResponseInterface $response): void
    {
        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        if ($body->isReadable()) {
            fpassthru($body->detach());
        }
    }
}
