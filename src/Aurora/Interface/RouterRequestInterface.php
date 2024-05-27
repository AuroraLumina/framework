<?php

namespace AuroraLumina\Interface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RouterRequestInterface extends RequestHandlerInterface, RouterInterface
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

    /**
     * Add a POST route to the application.
     *
     * @param string $path  The route path
     * @param mixed $action The route action
     * @return void
     */
    public function post(string $path, mixed $action): void;

    /**
     * Add a GET route to the application.
     *
     * @param string $path  The route path
     * @param mixed $action The route action
     * @return void
     */
    public function get(string $path, mixed $action): void;
}