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
     * The route handler that will be called when the route matches the request.
     *
     * @var string
     */
    private string $handler;

    /**
     * Route constructor.
     *
     * @param string $method The HTTP method accepted by the route.
     * @param string $path The route pattern.
     * @param string $handler The route handler.
     */
    public function __construct(string $method, string $path, string $handler)
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
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
     * Gets the route handler.
     *
     * @return string The route handler.
     */
    public function getHandler(): string
    {
        return $this->handler;
    }

    /**
     * Checks if the route matches the provided request.
     *
     * @param string $method The HTTP method of the request.
     * @param string $path The request path.
     * @return bool True if the route matches the request, false otherwise.
     */
    public function match(string $method, string $path): bool
    {
        if ($this->method === $method && $this->matchesPath($this->path, $path))
        {
            return true;
        }

        return false;
    }

    /**
     * Checks if the route pattern matches the provided path.
     *
     * @param string $pattern The route pattern.
     * @param string $path The request path.
     * @return bool True if it matches, false otherwise.
     */
    protected function matchesPath(string $pattern, string $path): bool
    {
        $pattern = preg_replace('#\(/\)#', '/?', $pattern);
        $pattern = '#^' . preg_quote($pattern, '#') . '$#';
        return (bool) preg_match($pattern, $path);
    }
}