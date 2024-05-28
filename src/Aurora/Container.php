<?php

namespace AuroraLumina;

use Exception;
use ReflectionClass;
use RuntimeException;
use Psr\Container\ContainerInterface;
use AuroraLumina\Interface\ServiceInterface;

class Container implements ContainerInterface
{
    /**
     * The container records.
     *
     * @var array<string|ServiceInterface>
     */
    protected $instances = [];

    /**
     * Constructor that accepts multiple instances of ServiceInterface.
     * 
     * This constructor utilizes the splat operator (...) to accept a variable number of 
     * ServiceInterface instances. Each provided instance is stored in the $instances property 
     * for later use.
     * 
     * @param ServiceInterface ...$services One or more instances of ServiceInterface to be managed.
     */
    public function __construct(ServiceInterface ...$services)
    {
        foreach ($services as $service)
        {
            $this->bind($service);
        }
    }

    /**
     * Get an instance from an id
     *
     * @param  string $service
     * @return string|ServiceInterface
     *
     * @throws Exception
     */
    public function get(string $service): string|ServiceInterface
    {
        if (!$this->has($service))
        {
            throw new Exception("Container has not found.");
        }

        return $this->instances[$service];
    }

    /**
     * Check if you have an instance.
     *
     * @param  string $service
     * @return void
     */
    public function has(string $service): bool
    {
        return array_key_exists($service, $this->instances);
    }
    
    /**
     * Binds a service to the container in a scoped manner.
     *
     * @param string $service The name of the service to bind.
     *
     * @return void
     *
     * @throws Exception If the service is already bound in the container.
     */
    public function bindScoped(string $service): void
    {
        if ($this->has($service))
        {
            throw new Exception("Service already bound in the container.");
        }

        // For a scoped binding, simply store the service name itself.
        $this->instances[$service] = $service;
    }

    /**
     * Bind an instance from an service
     *
     * @param ServiceInterface $service
     * @return void
     *
     * @throws RuntimeException
     */
    public function bind(string|ServiceInterface $service): void
    {
        if (is_string($service))
        {
            $reflectionClass = new ReflectionClass($service);
            $instance = $reflectionClass->newInstance();

            $this->validateService($instance);

            $this->instances[$service] = $instance;
        }

        if ($service instanceof ServiceInterface)
        {
            $class = get_class($service);

            if ($this->has($class))
            {
                throw new RuntimeException("Service already bound in the container.");
            }
            $this->instances[$class] = $service;
        }
    }

    /**
     * Validates a service instance to ensure it implements the ServiceInterface.
     *
     * @param mixed $instance The service instance to validate.
     *
     * @return void
     *
     * @throws \RuntimeException If the service instance does not implement ServiceInterface.
     */
    protected function validateService(mixed $instance): void
    {
        if (!$instance instanceof ServiceInterface)
        {
            throw new RuntimeException("Invalid service. Expected instance of ServiceInterface.");
        }
    }
}