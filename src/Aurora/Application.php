<?php

namespace AuroraLumina;

use AuroraLumina\Http\Emitter;
use AuroraLumina\Interface\ServiceInterface;
use AuroraLumina\Routing\Router;
use Psr\Http\Server\MiddlewareInterface;
use Laminas\Diactoros\ServerRequestFactory;

use Psr\Http\Server\RequestHandlerInterface;
use AuroraLumina\Middleware\MiddlewareDispatcher;
use Psr\Http\Message\ResponseInterface as Response;
use AuroraLumina\Middleware\AuthenticationMiddleware;
use AuroraLumina\Routing\Route;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class Application
{
    /**
     * Dependency injection container.
     *
     * @var Container
     */
    protected Container $container;

    /**
     * Router instance.
     *
     * @var Router
     */
    protected Router $router;

    /**
     * Middleware dispatcher instance.
     *
     * @var MiddlewareDispatcher
     */
    protected MiddlewareDispatcher $middlewareDispatcher;

    /**
     * Application constructor.
     *
     * @param Container $container The dependency injection container.
     * @param Router $router The router instance.
     * @param MiddlewareDispatcher $middlewareDispatcher The middleware dispatcher instance.
     */
    public function __construct(Container $container, Router $router, MiddlewareDispatcher $middlewareDispatcher)
    {
        $this->container = $container;
        $this->router = $router;
        $this->middlewareDispatcher = $middlewareDispatcher;
        $this->middlewareDispatcher->add(new AuthenticationMiddleware());
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
        $this->router->addRoute('GET', $path, $handler);
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
        return $this->middlewareDispatcher->handle($request);
    }

    /**
     * Run the application.
     *
     * @return void
     */
    public function run(): void
    {
        $request = ServerRequestFactory::fromGlobals();
        
        $response = $this->handle($request);
        
        $this->emitResponse($response);
    }

    /**
     * Emit the HTTP response.
     *
     * @param Response $response The HTTP response to emit
     * @return void
     */
    protected function emitResponse(Response $response): void
    {
        (new Emitter())->emit($response);
    }
}
