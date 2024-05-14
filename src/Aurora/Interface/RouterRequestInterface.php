<?php

namespace AuroraLumina\Interface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouterRequestInterface
{
    /**
     * Adds a middleware to the dispatcher.
     *
     * @param mixed $middleware The middleware to add.
     * @return MiddlewareDispatcherInterface This instance for method chaining.
     */
    public function add(string $method, string $path, string $handler): void;

    /**
     * Processes a request.
     *
     * @param ServerRequestInterface $request The request object.
     * @return ResponseInterface The response object.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;
}