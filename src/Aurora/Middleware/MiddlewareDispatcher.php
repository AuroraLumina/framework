<?php

namespace AuroraLumina\Middleware;

use AuroraLumina\Http\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AuroraLumina\Interface\MiddlewareDispatcherInterface;
use Laminas\Diactoros\Response as LaminasResponse;

class MiddlewareDispatcher implements MiddlewareDispatcherInterface
{
    /**
     * Array of middlewares.
     *
     * @var array
     */
    private array $middlewares = [];

    /**
     * Adds a middleware to the dispatcher.
     *
     * @param mixed $middleware The middleware to add
     * @return MiddlewareDispatcher This instance for method chaining
     */
    public function add($middleware): MiddlewareDispatcher
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Handles the request and returns a response.
     *
     * @param Request $request The incoming HTTP request
     * @return Response The HTTP response
     */
    public function handle(Request $request): Response
    {
        $response = new LaminasResponse();
        foreach ($this->middlewares as $middleware) {
            $response = $middleware->process($request, $this);
            if ($response instanceof Response) {
                return $response;
            }
        }
        
        return new EmptyResponse(404);
    }
}
