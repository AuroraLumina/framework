<?php

namespace AuroraLumina;

use AuroraLumina\Interface\ServiceInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class Container implements ContainerInterface
{
    /**
     * The container records.
     *
     * @var array<ServiceInterface>
     */
    protected $instances = [];

    /**
     * Logger instance.
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

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
     * @param  string  $id
     * @return ServiceInterface
     *
     * @throws \Exception
     */
    public function get(string $id): ServiceInterface
    {
        if (!$this->has($id))
        {
            throw new \Exception("Container has not found: $id");
        }

        return $this->instances[$id];
    }

    /**
     * Check if you have an instance.
     *
     * @param  string  $id
     * @return void
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->instances);
    }

    /**
     * Bind an instance from an id
     *
     * @param  ServiceInterfaceservice$id
     * @return void
     *
     * @throws \Exception
     */
    public function bind(ServiceInterface $service): void
    {
        $class = get_class($service);

        if ($this->has($class))
        {
            throw new \Exception("Instance has not found: $class");
        }

        $this->instances[$class] = $service;
    }
}