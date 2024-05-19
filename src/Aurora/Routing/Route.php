<?php

namespace AuroraLumina\Routing;

class Route
{
    /**
     * The HTTP method accepted by the route (e.g., GET, POST, etc.).
     *
     * @var string
     */
    private string $method;

    /**
     * The route pattern, typically a relative URL (e.g., '/users/{id}').
     *
     * @var string
     */
    private string $path;

    /**
     * Parameters extracted from the route path.
     *
     * @var array
     */
    private array $parameters = [];

    /**
     * The route action that will be called when the route matches the request.
     *
     * @var string
     */
    private mixed $action;

    /**
     * Route constructor.
     *
     * @param string $method The HTTP method accepted by the route.
     * @param string $path The route pattern.
     * @param string $action The route handler.
     */
    public function __construct(string $method, string $path, mixed $action)
    {
        $this->method = $method;
        $this->path = $path;
        $this->action = $action;
    }

    /**
     * Gets the HTTP method accepted by the route.
     *
     * @return string The HTTP method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Gets the route pattern.
     *
     * @return string The route pattern.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Gets the parameters extracted from the route path.
     *
     * @return array The parameters.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Gets the route handler.
     *
     * @return string The route handler.
     */
    public function getAction(): mixed
    {
        return $this->action;
    }

    /**
     * Sets the parameters extracted from the route path.
     *
     * @param array $parameters The parameters.
     * @return void
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Set a parameter to the route.
     *
     * @param string $key The name of the parameter.
     * @param mixed $value The value of the parameter.
     * @return void
     */
    public function setParameter(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Convert route path to regex pattern.
     *
     * @param string $path The route path.
     * @return string The regex pattern.
     */
    public function convertToRegex(string $path): string
    {
        return preg_replace_callback('/{([^}]+)}/', function ($matches) {
            return "(?P<{$matches[1]}>[^/]+)";
        }, $path);
    }

    /**
     * Check if route matches method and path.
     *
     * @param Route $route The route object.
     * @param string $method The request method.
     * @param string $path The request path.
     * @param string $pattern The regex pattern of the route.
     * @return bool True if route matches, false otherwise.
     */
    public function routeMatches(string $method, string $path, string $pattern): bool
    {
        return $this->method === $method &&
            preg_match("/^{$pattern}$/", $path);
    }

    /**
 * Extract parameters from path and set them in the route.
 *
 * @param Route $route The route object.
 * @param string $path The request path.
 * @return void
 */
public function extractAndSetParameters(Route $route, string $path): void
{
    preg_match_all('/{([^}]+)}/', $route->getPath(), $matches);
    $parameterNames = $matches[1];
    
    preg_match("/^{$this->convertToRegex($route->getPath())}$/", $path, $matches);
    array_shift($matches); // Remove full match
    
    foreach ($parameterNames as $index => $name) {
        $route->setParameter($name, $matches[$index]);
    }
}
}
