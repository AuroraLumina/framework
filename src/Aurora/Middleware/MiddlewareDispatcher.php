<?php

namespace AuroraLumina\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use AuroraLumina\Http\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AuroraLumina\Interface\MiddlewareDispatcherInterface;

class MiddlewareDispatcher implements MiddlewareDispatcherInterface
{
    /**
     * Array of middlewares.
     *
     * @var array<MiddlewareInterface>
     */
    private array $middlewares = [];

    /**
     * Adds a middleware to the dispatcher.
     *
     * @param mixed $middleware The middleware to add
     * @return MiddlewareDispatcherInterface This instance for method chaining
     */
    public function add(MiddlewareInterface $middleware): MiddlewareDispatcherInterface
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Creates a final request handler implementing the RequestHandlerInterface.
     *
     * @param Request $request The incoming HTTP request.
     * @return RequestHandlerInterface The final request handler.
     */
    private function finalRequest(ServerRequestInterface $request): RequestHandlerInterface
    {
        return new class($request) implements RequestHandlerInterface
        {
            /**
             * Handles the request by returning a 204 No Content response.
             *
             * @param Request $request The incoming HTTP request.
             * @return Response The HTTP response.
             */
            public function handle(Request $request): Response
            {
                return new EmptyResponse(204);
            }
        };
    }
    
    /**
     * Handles the request and returns a response.
     *
     * @param ServerRequestInterface $request The incoming HTTP request
     * @return ResponseInterface The HTTP response
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $finalHandler = $this->finalRequest($request);

        $middlewares = array_reverse($this->middlewares);

        foreach ($middlewares as $middleware)
        {
            $finalHandler = $middleware->process($request, $finalHandler);
        }

        return $finalHandler;
    }
}
