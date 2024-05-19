<?php

namespace AuroraLumina\Request;

use ReflectionClass;
use AuroraLumina\Container;
use AuroraLumina\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use AuroraLumina\Http\Response\Response;
use AuroraLumina\Controller\BaseController;
use AuroraLumina\Interface\ServiceInterface;
use Psr\Http\Message\ServerRequestInterface;
use AuroraLumina\Http\Response\EmptyResponse;
use AuroraLumina\Interface\ControllerInterface;
use AuroraLumina\Interface\RouterRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class RouterRequest implements RouterRequestInterface
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
     * 
     * @var ResponseInterface
     */
    protected ResponseInterface $response;

    /**
     * Constructs a new Router instance.
     *
     * @param Container $container The dependency injection container.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->response = new EmptyResponse();
    }

    /**
     * Adds a route to the router.
     *
     * @param string $method The HTTP method of the route.
     * @param string $path   The route path.
     * @param mixed $handler The route action
     * @return void
     */
    public function add(string $method, string $path, mixed $action): void
    {
        $this->routes[] = new Route($method, $path, $action);
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
        
        if (!$this->container->has($name))
        {
            throw new \RuntimeException("Dependency not found in the container.");
        }

        $service = $this->container->get($name);

        if (is_string($service))
        {
            $reflectionClass = new ReflectionClass($service);
            $instance = $reflectionClass->newInstance();

            $this->container->validateService($instance);

            return $instance;
        }

        return $service;
    }

    /**
     * Instantiate a controller class with its dependencies injected.
     *
     * @param string $class The controller class name.
     * @return mixed The instantiated controller.
     * @throws \RuntimeException If the controller cannot be instantiated.
     */
    protected function instantiateController(string $class): mixed
    {
        $reflectionClass = new ReflectionClass($class);

        $constructor = $reflectionClass->getConstructor();
        if (!$constructor || count($constructor->getParameters()) === 0)
        {
            return $reflectionClass->newInstance();
        }
        else
        {
            return $reflectionClass->newInstanceArgs(
                $this->resolveConstructorDependencies($constructor->getParameters())
            );
        }
    }

    /**
     * Validates a controller instance.
     *
     * @param mixed $controller The controller instance to validate.
     *
     * @throws \RuntimeException If the controller could not be instantiated or if it does not implement ControllerInterface.
     */
    protected function validateController(mixed $controller): void
    {
        if (!$controller)
        {
            throw new \RuntimeException("Controller could not be instantiated.");
        }

        if (!$controller instanceof ControllerInterface)
        {
            throw new \RuntimeException("Invalid controller. Expected instance of ControllerInterface.");
        }
    }

    /**
     * Validates the visibility of a method in a controller class.
     *
     * @param ControllerInterface $controller The controller instance.
     * @param string $method The name of the method to validate.
     *
     * @throws \RuntimeException If the method is not found in the controller class or if it is not public.
     */
    protected function validateMethod(ControllerInterface $controller, string $method): void
    {
        $reflectionMethod = $this->getReflectionMethod($controller, $method);

        if (!$reflectionMethod->isPublic()) {
            throw new \RuntimeException("Method in controller class is not public.");
        }
    }

    /**
     * Retrieves a reflection of the specified method in the controller class.
     *
     * @param ControllerInterface $controller The controller instance.
     * @param string $method The name of the method to retrieve.
     *
     * @return \ReflectionMethod The reflection of the specified method.
     *
     * @throws \RuntimeException If the method is not found in the controller class.
     */
    private function getReflectionMethod(ControllerInterface $controller, string $method): \ReflectionMethod
    {
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method not found in controller class.");
        }

        return new \ReflectionMethod($controller, $method);
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
     * @param mixed $action The action string.
     * @return callable The middleware callback.
     */
    protected function buildCallback(mixed $action): callable
    {
        return function (Request $request) use ($action) {
            if ($action instanceof \Closure) {
                return $action($request);
            }

            if (is_array($action)) {
                [$class, $method] = $action;

                $controller = $this->instantiateController($class);

                $this->validateController($controller);
                $this->validateMethod($controller, $method);

                return $this->callControllerMethod($controller, $method, $request);
            }

            return $action;
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
     * Processes a request.
     *
     * @param ServerRequestInterface $request The request object.
     * @return ResponseInterface The response object.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = $this->findRoute($request->getMethod(), $request->getUri()->getPath());

        if ($route)
        {
            $callback = $this->buildCallback($route->getAction())($request);

            if (is_string($callback))
            {
                $response = new Response();
                $response->getBody()->write($callback);
                return $response;
            }

            if ($callback instanceof ResponseInterface)
            {
                return $callback;
            }
        }

        return new EmptyResponse(404);
    }
}
