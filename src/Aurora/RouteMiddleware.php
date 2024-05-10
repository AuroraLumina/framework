<?php

namespace AuroraLumina;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteMiddleware implements MiddlewareInterface
{
    private string $method;
    private string $path;
    private $handler;

    public function __construct(string $method, string $path, $handler)
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
{
    // Check if request method matches
    if ($request->getMethod() !== $this->method) {
        // Method not allowed
        return new \Laminas\Diactoros\Response\EmptyResponse(405);
    }

    // Check if request path matches
    if ($request->getUri()->getPath() !== $this->path) {
        // Not found
        return new \Laminas\Diactoros\Response\EmptyResponse(404);
    }

    // Execute the handler
    if ($this->handler instanceof MiddlewareInterface) {
        // If the handler is a MiddlewareInterface, process it
        return $this->handler->process($request, $handler);
    } elseif ($this->handler instanceof \Closure) {
        // If the handler is a Closure, call it passing the request and handler
        return call_user_func($this->handler, $request, $handler);
    } else {
        // Invalid handler
        throw new \RuntimeException('Invalid handler type');
    }
}

}
