<?php

namespace AuroraLumina;

use AuroraLumina\Http\Emitter;
use AuroraLumina\Routing\Router;
use Laminas\Diactoros\ServerRequestFactory;

use AuroraLumina\Middleware\MiddlewareDispatcher;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Application
{
    protected Container $container;

    protected Router $router;
    
    protected MiddlewareDispatcher $middlewareDispatcher;

    /**
     * Application constructor.
     *
     * @param Container|null $container The application container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->router = new Router($container);
        $this->middlewareDispatcher = new MiddlewareDispatcher();
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
     * Handle the incoming request and return a response.
     *
     * @param  Request  $request The incoming HTTP request
     * @return Response The HTTP response
     */
    public function handle(Request $request): Response
    {
        return $this->router->handle($request);
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
