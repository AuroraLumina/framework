<?php

namespace AuroraLumina\Middleware;

use AuroraLumina\Routing\Router;
use Psr\Http\Server\MiddlewareInterface;
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
     * Constructs a new MiddlewareDispatcher instance.
     *
     * @param Router $router The router instance.
     */
    public function __construct(Router $router)
    {
        $this->middlewares[] = $router;
    }


    /**
     * Adds a middleware to the dispatcher.
     *
     * @param mixed $middleware The middleware to add
     * @return MiddlewareDispatcher This instance for method chaining
     */
    public function add($middleware): MiddlewareDispatcher
    {
        if ($middleware instanceof MiddlewareInterface)
        {
            $this->middlewares[] = $middleware;
        }
        return $this;
    }

    private function finalRequest(Request $request): RequestHandlerInterface
    {
        return new class($request) implements RequestHandlerInterface {
            public function handle(Request $request): Response
            {
                return new EmptyResponse(204);
            }
        };
    }
    
    /**
     * Handles the request and returns a response.
     *
     * @param Request $request The incoming HTTP request
     * @return Response The HTTP response
     */
    public function handle(Request $request): Response
    {
        $finalHandler = $this->finalRequest($request);

        $middlewares = array_reverse($this->middlewares);

        foreach ($middlewares as $middleware) {
            if ($middleware instanceof RequestHandlerInterface) {
                $finalHandler = $middleware->handle($request);
            } else {
                $finalHandler = $middleware->process($request, $finalHandler);
            }
        }

        return $finalHandler;
    }
}
