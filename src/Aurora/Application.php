<?php

namespace AuroraLumina;

use AuroraLumina\Http\Emitter;
use Psr\Http\Server\MiddlewareInterface;

use AuroraLumina\Interface\RouterInterface;
use AuroraLumina\Interface\ServiceInterface;
use AuroraLumina\Factory\ServerRequestFactory;
use AuroraLumina\Interface\ContainerInterface;
use AuroraLumina\Interface\RouterRequestInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AuroraLumina\Interface\MiddlewareDispatcherInterface;

class Application implements RouterInterface
{

    /**
     * Dependency injection container.
     *
     * @var Container
     */
    protected ContainerInterface $container;
    
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
     * Creates a new application instance.
     *
     * @param ContainerInterface $container The dependency injection container.
     * @param RouterRequestInterface $routerRequest The router request instance.
     * @param MiddlewareDispatcherInterface $middlewareDispatcher The middleware dispatcher instance.
     */
    public function __construct(ContainerInterface $container, RouterRequestInterface $routerRequest, MiddlewareDispatcherInterface $middlewareDispatcher)
    {
        $this->container = $container;
        $this->routerRequest = $routerRequest;
        $this->middlewareDispatcher = $middlewareDispatcher;
    }
    
    /**
     * Add a GET route to the application.
     *
     * @param string $path  The route path
     * @param mixed $action The route action
     * @return void
     */
    public function get(string $path, mixed $action): void
    {
        $this->routerRequest->add('GET', $path, $action);
    }
    
    /**
     * Add a POST route to the application.
     *
     * @param string $path  The route path
     * @param mixed $action The route action
     * @return void
     */
    public function post(string $path, mixed $action): void
    {
        $this->routerRequest->add('POST', $path, $action);
    }
    
    /**
     * Binds a service to the container.
     *
     * @param ServiceInterface $service The service to be bound.
     * @return void
     */
    public function bind(ServiceInterface $service): void
    {
        $this->container->bind($service);
    }
    
    /**
     * Binds a scoped service to the container.
     *
     * @param ServiceInterface $service The service to be bound.
     * 
     * @return void
     */
    public function bindScoped(string $service): void
    {
        $this->container->bind($service);
    }

    /**
     * Bind a configuration object.
     *
     * @param object $configuration The configuration object to bind.
     * 
     * @return void
     * 
     * @throws Exception If the provided configuration is not a valid class instance or if it is an instance of stdClass.
     */
    public function configuration(object $data): void
    {
        $this->container->configuration($data);
    }
    
    /**
     * Adds a middleware to the middleware chain.
     *
     * @param MiddlewareInterface $middleware The middleware to be added.
     * 
     * @return void
     */
    public function addMiddleware(MiddlewareInterface $middleware): void
    {
        $this->middlewareDispatcher->add($middleware);
    }
    
    /**
     * Handle the incoming request and return a response.
     *
     * @param Request $request The incoming HTTP request
     * @return Response The HTTP response
     */
    public function handle(Request $request): Response
    {
        $response = $this->middlewareDispatcher->handle($request);
        return $response;
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
     * @param bool     $cleanDebuff Clear output
     * @return void
     */
    protected function emitResponse(Response $response, bool $cleanDebuff): void
    {
        (new Emitter())->emit($response, $cleanDebuff);
    }
}