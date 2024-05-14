<?php

namespace AuroraLumina\Middleware;

use AuroraLumina\Application;
use InvalidArgumentException;
use AuroraLumina\Routing\Router;
use Psr\Http\Server\MiddlewareInterface;
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
     * The Router instance.
     *
     * @var Router
     */
    private Router $router;

    /**
     * Constructs a new MiddlewareDispatcher instance.
     *
     * @param Router $router The router instance.
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }


    /**
     * Adds a middleware to the dispatcher.
     *
     * @param mixed $middleware The middleware to add
     * @return MiddlewareDispatcher This instance for method chaining
     */
    public function add($middleware): MiddlewareDispatcher
    {
        if (!$middleware instanceof MiddlewareInterface)
        {
            throw new InvalidArgumentException("{$middleware} is not an instance of MiddlewareInterface");
        }
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
        $finalHandler = $this->router;
        
        $middlewares = array_reverse($this->middlewares);
        
        foreach ($middlewares as $middleware)
        {
            $finalHandler = $middleware->process($request, $finalHandler);
        }
        
        return $finalHandler;
    }
}
