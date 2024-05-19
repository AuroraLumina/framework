<?php

namespace AuroraLumina\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use AuroraLumina\Http\Response\EmptyResponse;
use AuroraLumina\Interface\RouterRequestInterface;
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
     * Array of closures
     * 
     * @var array<Closure>
     */
    private array $closures = [];
    
    /**
     * Router request instance.
     *
     * @var RouterRequestInterface
     */
    private RouterRequestInterface $routerRequest;
    
    /**
     * Application constructor.
     *
     * @param RouterRequestInterface $routerRequest The router request instance.
     */
    public function __construct(RouterRequestInterface $routerRequest)
    {
        $this->routerRequest = $routerRequest;
    }
    
    /**
     * Adds a middleware to the dispatcher.
     *
     * @param MiddlewareInterface $middleware The middleware to add
     * @return MiddlewareDispatcherInterface This instance for method chaining
     */
    public function add(MiddlewareInterface $middleware): MiddlewareDispatcherInterface
    {
        $this->middlewares[] = $middleware;
        return $this;
    }
    
    /**
     * Handles the request and returns a response.
     *
     * @param ServerRequestInterface $request The incoming HTTP request
     * @return ResponseInterface The HTTP response
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // $finalHandler RequestHandlerInterface intercae
        $finalHandler = $this->routerRequest;
        
        if (strtoupper($request->getMethod()) === 'HEAD')
        {
            return new EmptyResponse(204);
        }

        $middlewares = array_reverse($this->middlewares);

        $closures = array_reverse($this->closures);

        foreach ($middlewares as $middleware)
        {
            $finalHandler = $middleware->process($request, $finalHandler);
        }

        foreach ($closures as $closure)
        {
            $closure();
        }

        return $finalHandler->handle($request);
    }
}
