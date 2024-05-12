<?php

namespace AuroraLumina;

use ReflectionClass;
use AuroraLumina\Http\Emitter;
use Laminas\Diactoros\ServerRequestFactory;

// Use para Response e ServerRequest
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response as LaminasResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Application
{
    protected ?Container $container;
    protected array $middlewares = [];

    /**
     * Application constructor.
     *
     * @param Container|null $container The application container
     */
    public function __construct(?Container $container = null)
    {
        $this->container = $container;
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
        $this->middlewares[] = new RouteMiddleware(
            'GET',
            $path,
            $this->buildCallback($handler)
        );
    }

    /**
     * Build a middleware callback for the given handler.
     *
     * @param string $handler The handler string in format "Class::method"
     * @return callable The middleware callback
     */
    protected function buildCallback(string $handler): callable
    {
        return function (Request $request) use ($handler)
        {
            [$class, $method] = explode('::', $handler);

            $controller = $this->instantiateController($class);
            $this->validateMethod($controller, $method, $class);

            return $this->callControllerMethod($controller, $method, $request);
        };
    }

    /**
     * Instantiate a controller class with its dependencies injected.
     *
     * @param string $class The controller class name
     * @return object The instantiated controller
     */
    protected function instantiateController(string $class)
    {
        $reflectionClass = new ReflectionClass($class);

        if ($constructor = $reflectionClass->getConstructor()) {
            return $reflectionClass->newInstanceArgs(
                $this->resolveConstructorDependencies($constructor->getParameters())
            );
        }

        return $constructor;
    }

    /**
     * Resolve constructor dependencies for a given set of parameters.
     *
     * @param array $params The constructor parameters
     * @return array The resolved dependencies
     */
    protected function resolveConstructorDependencies(array $params): array
    {
        return array_map([$this, 'resolveDependency'], $params);
    }

    /**
     * Resolve a single constructor parameter dependency.
     *
     * @param \ReflectionParameter $param The parameter to resolve
     * @return mixed The resolved dependency
     */
    protected function resolveDependency(\ReflectionParameter $param)
    {
        $name = $param->getType()->getName();
        return $this->container->get($name);
    }

    /**
     * Validate if the method exists and is public in the given controller class.
     *
     * @param object $controller The controller object
     * @param string $method The method name
     * @param string $class The class name
     * @return void
     * @throws \RuntimeException If method doesn't exist or is not public
     */
    private function validateMethod($controller, string $method, string $class): void
    {
        // Method existence check
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method '{$method}' not found in class '{$class}'");
        }

        // Method visibility check
        $reflectionMethod = new \ReflectionMethod($controller, $method);
        if (!$reflectionMethod->isPublic()) {
            throw new \RuntimeException("Method '{$method}' in class '{$class}' is not public");
        }
    }

    /**
     * Call the specified method on the controller object.
     *
     * @param object $controller The controller object
     * @param string $method The method name
     * @param Request $request The request object
     * @return Response The response object
     */
    private function callControllerMethod($controller, string $method, Request $request): Response
    {
        return $controller->{$method}($request);
    }

    /**
     * Handle the incoming request and return a response.
     *
     * @param  Request  $request The incoming HTTP request
     * @return Response The HTTP response
     */
    public function handle(Request $request): Response
    {
        $requestHandler = $this->buildRequestHandler();
        
        return $requestHandler->handle($request);
    }

    /**
     * Build the request handler to process middlewares.
     *
     * @return RequestHandlerInterface The request handler
     */
    protected function buildRequestHandler(): RequestHandlerInterface
    {
        $middlewares = $this->middlewares;

        return new class($middlewares) implements RequestHandlerInterface {
            private array $middlewares;

            public function __construct(array $middlewares)
            {
                $this->middlewares = $middlewares;
            }

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
        };
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
