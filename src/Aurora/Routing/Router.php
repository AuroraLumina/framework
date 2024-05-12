<?php

namespace AuroraLumina\Routing;

use ReflectionClass;
use AuroraLumina\Container;
use AuroraLumina\Http\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Router
{
    /**
     * @var Container The dependency injection container.
     */
    protected Container $container;
    
    /**
     * @var array An array to store route objects.
     */
    protected array $routes = [];

    /**
     * Router constructor.
     *
     * @param Container $container The dependency injection container.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Adds a route to the router.
     *
     * @param string $method The HTTP method of the route.
     * @param string $path The route path.
     * @param string $handler The handler string in format "Class::method".
     * @return void
     */
    public function addRoute(string $method, string $path, string $handler): void
    {
        $this->routes[] = new Route($method, $path, $handler);
    }

    /**
     * Resolve constructor dependencies for a given set of parameters.
     *
     * @param array $params The constructor parameters.
     * @return array The resolved dependencies.
     */
    protected function resolveConstructorDependencies(array $params): array
    {
        return array_map([$this, 'resolveDependency'], $params);
    }

    /**
     * Resolve a single constructor parameter dependency.
     *
     * @param \ReflectionParameter $param The parameter to resolve.
     * @return mixed The resolved dependency.
     */
    protected function resolveDependency(\ReflectionParameter $param)
    {
        $name = $param->getType()->getName();
        return $this->container->get($name);
    }

    /**
     * Instantiate a controller class with its dependencies injected.
     *
     * @param string $class The controller class name.
     * @return object The instantiated controller.
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
     * Validate if the method exists and is public in the given controller class.
     *
     * @param object $controller The controller object.
     * @param string $method The method name.
     * @param string $class The class name.
     * @return void
     * @throws \RuntimeException If method doesn't exist or is not public.
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
     * @param object $controller The controller object.
     * @param string $method The method name.
     * @param Request $request The request object.
     * @return Response The response object.
     */
    private function callControllerMethod($controller, string $method, Request $request): Response
    {
        return $controller->{$method}($request);
    }

    /**
     * Build a middleware callback for the given handler.
     *
     * @param string $handler The handler string in format "Class::method".
     * @return callable The middleware callback.
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
     * Find a route that matches the given request method and path.
     *
     * @param string $method The request method.
     * @param string $path The request path.
     * @return Route|null The matching route or null if no match was found.
     */
    protected function findRoute(string $method, string $path): ?Route
    {
        foreach ($this->routes as $route)
        {
            if ($route->match($method, $path))
            {
                return $route;
            }
        }

        return null;
    }

    /**
     * Handle a request.
     *
     * @param Request $request The request object.
     * @return Response The response object.
     */
    public function handle(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        if ($route = $this->findRoute($method, $path))
        {
            $callback = $this->buildCallback($route->getHandler());
            return $callback($request);
        }

        return new EmptyResponse(404);
    }
}
