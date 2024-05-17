<?php

namespace AuroraLumina;

use AuroraLumina\Http\Emitter;
use Psr\Http\Server\MiddlewareInterface;

use Laminas\Diactoros\ServerRequestFactory;
use AuroraLumina\Interface\ServiceInterface;
use AuroraLumina\Interface\RouterRequestInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AuroraLumina\Interface\MiddlewareDispatcherInterface;

class Application
{
    /**
     * Dependency injection container.
     *
     * @var Container
     */
    protected Container $container;

    /**
     * Router request instance.
     *
     * @var RouterRequestInterface
     */
    protected RouterRequestInterface $routerRequest;

    /**
     * Middleware dispatcher instance.
     *
     * @var MiddlewareDispatcherInterface
     */
    protected MiddlewareDispatcherInterface $middlewareDispatcher;

    /**
     * Application constructor.
     *
     * @param Container $container The dependency injection container.
     * @param RouterRequestInterface $routerRequest The router request instance.
     * @param MiddlewareDispatcherInterface $middlewareDispatcher The middleware dispatcher instance.
     */
    public function __construct(Container $container,
                                RouterRequestInterface $routerRequest,
                                MiddlewareDispatcherInterface $middlewareDispatcher)
    {
        $this->container = $container;
        $this->routerRequest = $routerRequest;
        $this->middlewareDispatcher = $middlewareDispatcher;
    }

    /**
     * Add a GET route to the application.
     *
     * @param string $path    The route path
     * @param string $handler The route handler
     * @return void
     */
    public function get(string $path, string $handler): void
    {
        $this->routerRequest->add('GET', $path, $handler);
    }

    /**
     * Binds a service to the container.
     *
     * @param ServiceInterface $service The service to be bound.
     * @return void
     */
    public function bindContainer(ServiceInterface $service): void
    {
        $this->container->bind($service);
    }

    /**
     * Adds a middleware to the middleware chain.
     *
     * @param MiddlewareInterface $middleware The middleware to be added.
     * @return void
     */
    public function addMiddleware(MiddlewareInterface $middleware): void
    {
        // Adds the middleware to the middleware chain in the dispatcher
        $this->middlewareDispatcher->add($middleware);
    }


    /**
     * Handle the incoming request and return a response.
     *
     * @param  Request  $request The incoming HTTP request
     * @return Response The HTTP response
     */
    public function handle(Request $request): Response
    {
        return $this->middlewareDispatcher->handle($request);;
    }

    /**
     * Run the application.
     *
     * @param bool $cleanDebuff Clear output
     * @return void
     */
    public function run(bool $cleanDebuff = true): void
    {
        $request = ServerRequestFactory::fromGlobals();
        
        $response = $this->handle($request);
        
        $this->emitResponse($response, $cleanDebuff);
    }

    /**
     * Emit the HTTP response.
     *
     * @param Response $response The HTTP response to emit
     * @param bool $cleanDebuff Clear output
     * @return void
     */
    protected function emitResponse(Response $response, bool $cleanDebuff): void
    {
        (new Emitter())->emit($response, $cleanDebuff);
    }
}
