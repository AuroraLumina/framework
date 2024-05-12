<?php

namespace AuroraLumina\Http;

use Psr\Http\Message\ResponseInterface;

class Emitter
{
    /**
     * Emit an HTTP response to the client.
     *
     * @param ResponseInterface $response The HTTP response to emit.
     * @return void
     */
    public function emit(ResponseInterface $response): void
    {
        $this->cleanOutputBuffer();
        
        $this->sendStatusLine($response);
        
        $this->sendHeaders($response);
        
        echo $response->getBody();
    }

    /**
     * Clean the output buffer, if necessary.
     *
     * @return void
     */
    private function cleanOutputBuffer(): void
    {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    /**
     * Send the status line of the HTTP response.
     *
     * @param ResponseInterface $response The HTTP response.
     * @return void
     */
    private function sendStatusLine(ResponseInterface $response): void
    {
        $protocol = $response->getProtocolVersion();
        $statusCode = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();
        header("HTTP/{$protocol} {$statusCode} {$reasonPhrase}", true, $statusCode);
    }

    /**
     * Send the headers of the HTTP response.
     *
     * @param ResponseInterface $response The HTTP response.
     * @return void
     */
    private function sendHeaders(ResponseInterface $response): void
    {
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("{$name}: {$value}", false);
            }
        }
        
        foreach ($response->getHeader('Set-Cookie') as $value) {
            header("Set-Cookie: {$value}", false);
        }
    }
}
