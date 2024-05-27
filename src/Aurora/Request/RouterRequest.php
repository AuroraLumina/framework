<?php

namespace AuroraLumina\Request;

use Closure;
use stdClass;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use ReflectionParameter;
use AuroraLumina\Container;
use AuroraLumina\Routing\Route;
use Psr\Http\Message\RequestInterface;
use AuroraLumina\Request\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use AuroraLumina\Http\Response\Response;
use AuroraLumina\Interface\ServiceInterface;
use AuroraLumina\Http\Response\EmptyResponse;
use AuroraLumina\Interface\ControllerInterface;
use AuroraLumina\Interface\RouterRequestInterface;

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
     * @param string $path   The route path.
     * @param mixed $handler The route action
     * @return void
     */
    public function add(string $method, string $path, mixed $action): void
    {
        $this->routes[] = new Route($method, $path, $action);
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
        $this->add('GET', $path, $action);
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
        $this->add('POST', $path, $action);
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
     * @param ReflectionParameter $param The parameter to resolve.
     * @return ServiceInterface The resolved dependency.
     * @throws RuntimeException If the dependency cannot be resolved.
     */
    protected function resolveDependency(ReflectionParameter $param): ServiceInterface
    {
        $name = $param->getType()->getName();
        
        if (!$this->container->has($name))
        {
            throw new RuntimeException("Dependency not found in the container.");
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
     * @throws RuntimeException If the controller could not be instantiated or if it does not implement ControllerInterface.
     */
    protected function validateController(mixed $controller): void
    {
        if (!$controller)
        {
            throw new RuntimeException("Controller could not be instantiated.");
        }

        if (!$controller instanceof ControllerInterface)
        {
            throw new RuntimeException("Invalid controller. Expected instance of ControllerInterface.");
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
            throw new RuntimeException("Method in controller class is not public.");
        }
    }

    /**
     * Retrieves a reflection of the specified method in the controller class.
     *
     * @param ControllerInterface $controller The controller instance.
     * @param string $method The name of the method to retrieve.
     *
     * @return ReflectionMethod The reflection of the specified method.
     *
     * @throws RuntimeException If the method is not found in the controller class.
     */
    private function getReflectionMethod(ControllerInterface $controller, string $method): ReflectionMethod
    {
        if (!method_exists($controller, $method)) {
            throw new RuntimeException("Method not found in controller class.");
        }

        return new ReflectionMethod($controller, $method);
    }

    /**
     * Call the specified method on the controller object.
     *
     * @param object $controller The controller object.
     * @param string $method The method name.
     * @param ServerRequest $request The request object.
     * @return Response The response object.
     */
    private function callControllerMethod($controller, string $method, ServerRequest $request, $args): Response
    {
        return $controller->{$method}($request, $args);
    }

    /**
     * Build a middleware callback for the given handler.
     *
     * @param mixed $action The action string.
     * @param array $parameters The router parameters.
     * @return callable The middleware callback.
     */
    protected function buildCallback(mixed $action): callable
    {
        return function (ServerRequest $request, array $parameters) use ($action)
        {
            if ($action instanceof Closure)
            {
                return $action($request, $parameters);
            }
    
            if (is_array($action))
            {
                [$class, $method] = $action;
    
                $controller = $this->instantiateController($class);
    
                $this->validateController($controller);
                $this->validateMethod($controller, $method);

                return $this->callControllerMethod($controller, $method, $request, $parameters);
            }
    
            return $action;
        };
    }

    /**
     * Build a regex pattern for route parameters.
     *
     * @param string $path The route path.
     * @return string The regex pattern.
     */
    private function buildRegexPattern(string $path): string
    {
        return preg_replace_callback('/{([^}]+)}/', function ($matches)
        {
            return "(?P<{$matches[1]}>[^/]+)";
        }, $path);
    }

    /**
     * Escape special characters in a regex pattern.
     *
     * @param string $pattern The regex pattern.
     * @return string The escaped regex pattern.
     */
    private function escapeRegex(string $pattern): string
    {
        return str_replace('/', '\/', $pattern);
    }

    /**
     * Add regex delimiters for matching the entire path.
     *
     * @param string $pattern The regex pattern.
     * @return string The pattern with delimiters.
     */
    private function addRegexDelimiters(string $pattern): string
    {
        return "^" . $pattern . "$";
    }

    /**
     * Check if a route matches the given method and path.
     *
     * @param Route $route The route object.
     * @param string $method The HTTP method of the request.
     * @param string $path The request path.
     * @param string $pattern The regex pattern of the route.
     * @return bool True if the route matches, false otherwise.
     */
    private function routeMatches(Route $route, string $method, string $path, string $pattern): bool
    {
        if ($this->matchesPath($path, $pattern))
        {
            $this->setRouteParametersBasedOnMethod($route, $method);
            
            $this->setRouteParametersFromPath($route, $path, $pattern);
            
            return true;
        }
        
        return false;
    }

    /**
     * Check if a route matches the given method and path.
     *
     * @param Route $route The route object.
     * @param string $method The HTTP method of the request.
     * @param string $path The request path.
     * @param string $pattern The regex pattern of the route.
     * @return bool True if the route matches, false otherwise.
     */
    private function matchesPath(string $path, string $pattern): bool
    {
        return preg_match('/' . $pattern . '/', $path, $matches);
    }

    /**
     * Set route parameters based on the request method.
     *
     * @param Route $route The route object.
     * @param string $method The HTTP method of the request.
     * @return void
     */
    private function setRouteParametersBasedOnMethod(Route $route, string $method): void
    {
        if ($method === 'POST')
        {
            // Set route parameters from $_POST
            foreach ($_POST as $key => $value)
            {
                $route->setParameter($key, $value);
            }
        }
    }

    /**
     * Set route parameters extracted from the path.
     *
     * @param Route $route The route object.
     * @param string $path The request path.
     * @param string $pattern The regex pattern of the route.
     * @return void
     */
    private function setRouteParametersFromPath(Route $route, string $path, string $pattern): void
    {
        // Set route parameters from the request path
        preg_match('/' . $pattern . '/', $path, $matches);
        
        foreach ($matches as $key => $value)
        {
            if (!is_numeric($key))
            {
                $route->setParameter($key, $value);
            }
        }
    }

    /**
     * Find the status code of the matching route.
     *
     * @param string $method The request method.
     * @param string $path The request path.
     * @return int The status code.
     */
    protected function findMatchingRouteStatus(string $method, string $path): int
    {
        $status = 404;
        
        foreach ($this->routes as $route)
        {
            $pattern = $this->buildRegexPattern($route->getPath());
            $escapedPattern = $this->escapeRegex($pattern);
            $fullPattern = $this->addRegexDelimiters($escapedPattern);

            if ($this->routeMatches($route, $method, $path, $fullPattern))
            {
                if ($route->getMethod() !== $method)
                {
                    $status = 405;
                }
                else
                {
                    $status = 200;
                }
            }
        }

        return $status;
    }

    /**
     * Find the matching route object.
     *
     * @param string $method The request method.
     * @param string $path The request path.
     * @return Route|null The matching route object, or null if no match is found.
     */
    protected function findMatchingRoute(string $method, string $path): ?Route
    {
        $matchingRoute = null;

        foreach ($this->routes as $route)
        {
            $pattern = $this->buildRegexPattern($route->getPath());
            $escapedPattern = $this->escapeRegex($pattern);
            $fullPattern = $this->addRegexDelimiters($escapedPattern);

            if ($this->routeMatches($route, $method, $path, $fullPattern))
            {
                if ($route->getMethod() === $method) {
                    $matchingRoute = $route;
                }
            }
        }

        return $matchingRoute;
    }

    /**
     * Find the matching route.
     *
     * @param string $method The request method.
     * @param string $path The request path.
     * @return stdClass An object containing the status and the matching route.
     */
    protected function findRoute(string $method, string $path): stdClass
    {
        $result = new stdClass();
        $result->status = $this->findMatchingRouteStatus($method, $path);
        $result->route = $this->findMatchingRoute($method, $path);
        return $result;
    }

    /**
     * Processes a request.
     *
     * @param ServerRequest $request The request object.
     * @return ResponseInterface The response object.
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        $result = $this->findRoute($request->getMethod(), $request->getUri()->getPath());

        if ($result->route)
        {
            $route = $result->route;

            $callback = $this->buildCallback($route->getAction())($request, $route->getParameters());

            if (is_string($callback))
            {
                $response = new Response($result->status);
                $response->getBody()->write($callback);
                return $response;
            }

            if ($callback instanceof ResponseInterface)
            {
                return $callback;
            }
        }

        return new EmptyResponse($result->status ? $result->status : 404);
    }
}
