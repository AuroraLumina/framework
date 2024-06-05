<?php

namespace AuroraLumina;

use stdClass;
use Exception;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use AuroraLumina\Request\ServerRequest;
use AuroraLumina\Interface\ContainerInterface;
use AuroraLumina\Request\RequestArguments;

/**
 * A simple dependency injection container.
 */
class Container implements ContainerInterface
{
    /**
     * The container records.
     *
     * @var array<object>
     */
    protected $instances = [];

    
    /**
     * The configuration records.
     *
     * @var array<mixed>
     */
    protected $configurations = [];

    /**
     * Constructor that accepts multiple instances of objects.
     * 
     * This constructor utilizes the splat operator (...) to accept a variable number of 
     * object instances. Each provided instance is stored in the $instances property 
     * for later use.
     * 
     * @param object ...$services One or more instances of objects to be managed.
     */
    public function __construct(object ...$services)
    {
        foreach ($services as $service)
        {
            $this->bind($service);
        }
    }

    /**
     * Get an instance by its id.
     *
     * @param  string $service The id of the instance.
     * 
     * @return mixed The instance.
     *
     * @throws Exception If the instance is not found.
     */
    public function get(string $service): mixed
    {
        if (!$this->has($service))
        {
            throw new Exception("Container has not found.");
        }

        return $this->instances[$service];
    }

    /**
     * Check if an instance exists.
     *
     * @param  string $service The id of the instance.
     * 
     * @return bool True if the instance exists, false otherwise.
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

        $this->validateServiceClassName($service);

        // For a scoped binding, simply store the service name itself.
        $this->instances[$service] = $service;
    }
    
    /**
     * Bind an instance from a service.
     *
     * @param object $service The service to bind.
     * 
     * @return void
     *
     * @throws Exception If the provided service is invalid.
     */
    public function bind(object $service): void
    {
        $this->validateServiceObject($service);

        $class = is_string($service) ? $service : get_class($service);

        if ($this->has($class))
        {
            throw new Exception("Service already bound in the container.");
        }
        
        $this->instances[$class] = $service;
    }

    /**
     * Validate if the provided class name is valid.
     *
     * @param string $class The class name to validate.
     * 
     * @return void
     * 
     * @throws Exception If the provided class name is not a valid class instance or if it is an instance of stdClass.
     */
    protected function validateServiceClassName(string $class): void
    {
        if (!class_exists($class))
        {
            throw new Exception("The provided must be a valid class instance.");
        }

        if ($class === stdClass::class)
        {
            throw new Exception("stdClass instances are not allowed.");
        }
    }

    /**
     * Validate if the provided object is valid for binding.
     *
     * @param object $service The object to validate.
     * 
     * @return void
     * 
     * @throws Exception If the provided object is an instance of stdClass.
     */
    protected function validateServiceObject(object $service): void
    {
        if ($service instanceof stdClass)
        {
            throw new Exception("Instances of stdClass are not allowed.");
        }
    }

    /**
     * Resolve constructor dependencies for a given set of parameters.
     *
     * @param array<ReflectionParameter> $params The constructor parameters.
     * @param array<mixed> $objects Optional objects to pass directly without resolving from the container.
     * 
     * @return array The resolved dependencies.
     */
    public function resolveConstructorDependencies(array $params, array $objects = []): array
    {
        return array_map(function($param) use ($objects)
        {
            return $this->resolveDependency($param, $objects);
        }, $params);
    }

    /**
     * Resolve a dependency by its type hint.
     *
     * @param ReflectionParameter $param The parameter representing the dependency.
     * @param array<mixed> $objects Optional objects to pass directly without resolving from the container.
     * 
     * @return mixed The resolved dependency instance.
     * 
     * @throws Exception If the dependency or its configuration is not found in the container.
     */
    protected function resolveDependency(ReflectionParameter $param, array $objects): mixed
    {

        $paramType = $param->getType();

        $className = $paramType->getName();

        foreach ($objects as $object)
        {
            if ($className == get_class($object))
            {
                return $object;
            }
        }

        $name = $param->getType()->getName();

        if ($this->hasConfiguration($name))
        {
            $configuration = $this->getConfiguration($name);
            return $configuration;
        }
        
        if (!$this->has($name))
        {
            throw new Exception("Dependency not found in the container.");
        }

        $service = $this->get($name);

        if (is_string($service))
        {
            $reflectionClass = new ReflectionClass($service);

            $constructor = $reflectionClass->getConstructor();

            if (!$constructor || count($constructor->getParameters()) === 0)
            {
                $instance = $reflectionClass->newInstance();
            }
            else
            {
                $instance = $reflectionClass->newInstanceArgs(
                    $this->resolveConstructorDependencies($constructor->getParameters())
                );
            }

            return $instance;
        }

        return $service;
    }

    /**
     * Check if an configuration exists.
     *
     * @param  string $key The key of the configuration.
     * 
     * @return bool True if the configuration exists, false otherwise.
     */
    public function hasConfiguration(string $key): bool
    {
        return array_key_exists($key, $this->configurations);
    }

    /**
     * Get a configuration by its key.
     *
     * @param  string $key The key of the configuration.
     * 
     * @return mixed The configuration.
     *
     * @throws Exception If the configuration is not found.
     */
    public function getConfiguration(string $key): mixed
    {
        if (!$this->hasConfiguration($key))
        {
            throw new Exception("Configuration has not found.");
        }

        return $this->configurations[$key];
    }

    /**
     * Bind a configuration object.
     *
     * @param object $configuration The configuration object to bind.
     * 
     * @return void
     * 
     * @throws Exception If the provided configuration is not a valid class instance or if it is an instance of stdClass.
     */
    public function configuration(object $configuration): void
    {
        $class = get_class($configuration);

        if (!class_exists($class))
        {
            throw new Exception("The provided configuration must be a valid class instance.");
        }

        if ($configuration instanceof stdClass)
        {
            throw new Exception("stdClass instances are not allowed as configurations.");
        }

        $this->configurations[$class] = $configuration;
    }
}