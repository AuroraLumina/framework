<?php

namespace AuroraLumina\Routing;

use ReflectionClass;
use AuroraLumina\Container;
use AuroraLumina\Interface\ServiceInterface;
use Psr\Http\Server\RequestHandlerInterface;
use AuroraLumina\Http\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Router implements RequestHandlerInterface
{
    /**
     * The dependency injection container.
     *
     * @var Container|null
     */
    protected ?Container $container;

    /**
     * An array to store route objects.
     *
     * @var array
     */
    protected array $routes = [];

    /**
     * Constructs a new Router instance.
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
     * @return ServiceInterface The resolved dependency.
     * @throws \RuntimeException If the dependency cannot be resolved.
     */
    protected function resolveDependency(\ReflectionParameter $param): ServiceInterface
    {
        $name = $param->getType()->getName();
        if (!$this->container->has($name)) {
            throw new \RuntimeException("Dependency '$name' not found in the container");
        }
        return $this->container->get($name);
    }

    /**
     * Instantiate a controller class with its dependencies injected.
     *
     * @param string $class The controller class name.
     * @return object|null The instantiated controller.
     * @throws \RuntimeException If the controller cannot be instantiated.
     */
    protected function instantiateController(string $class): ?object
    {
        $reflectionClass = new ReflectionClass($class);

        $constructor = $reflectionClass->getConstructor();
        if (!$constructor || count($constructor->getParameters()) === 0) {
            // If the class has no constructor or no parameters, instantiate without arguments.
            return $reflectionClass->newInstance();
        } else {
            // If the class has constructor parameters, resolve them.
            return $reflectionClass->newInstanceArgs(
                $this->resolveConstructorDependencies($constructor->getParameters())
            );
        }
    }

    /**
     * Validate if the method exists and is public in the given controller class.
     *
     * @param object $controller The controller object.
     * @param string $method The method name.
     * @param string $class The class name.
     * @return void
     * @throws \RuntimeException If the method doesn't exist or is not public.
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
        return function (Request $request) use ($handler) {
            [$class, $method] = explode('::', $handler);

            $controller = $this->instantiateController($class);
            if (!$controller) {
                throw new \RuntimeException("Controller '{$class}' could not be instantiated");
            }

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
        foreach ($this->routes as $route) {
            if ($route->match($method, $path)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Processes a request.
     *
     * @param Request $request The request object.
     * @return Response The response object.
     */
    public function handle(Request $request): Response
    {
        var_dump('bb');
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        if ($route = $this->findRoute($method, $path)) {
            $callback = $this->buildCallback($route->getHandler());
            return $callback($request);
        }

        return new EmptyResponse(404);
    }
}
