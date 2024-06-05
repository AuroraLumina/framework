<?php

namespace AuroraLumina\Interface;

use AuroraLumina\Request\ServerRequest;
use AuroraLumina\Request\RequestArguments;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Interface for a container that manages object instances.
 */
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Get an instance by its id.
     *
     * @param string $service The id of the instance.
     * 
     * @return mixed The instance.
     *
     * @throws Exception If the instance is not found.
     */
    public function get(string $service): mixed;

    /**
     * Check if an instance exists.
     *
     * @param string $service The id of the instance.
     * 
     * @return bool True if the instance exists, false otherwise.
     */
    public function has(string $service): bool;
    
    /**
     * Binds a service to the container in a scoped manner.
     *
     * @param string $service The name of the service to bind.
     *
     * @return void
     *
     * @throws Exception If the service is already bound in the container.
     */
    public function bindScoped(string $service): void;

    /**
     * Bind an instance from a service.
     *
     * @param object $service The service to bind.
     * 
     * @return void
     *
     * @throws Exception If the provided service is invalid.
     */
    public function bind(object $service): void;

    /**
     * Bind a configuration object.
     *
     * @param object $configuration The configuration object to bind.
     * 
     * @return void
     * 
     * @throws Exception If the provided configuration is not a valid class instance or if it is an instance of stdClass.
     */
    public function configuration(object $configuration): void;

    /**
     * Resolve constructor dependencies for a given set of parameters.
     *
     * @param array<ReflectionParameter> $params The constructor parameters.
     * @param array<mixed> $objects Optional objects to pass directly without resolving from the container.
     * 
     * @return array The resolved dependencies.
     */
    public function resolveConstructorDependencies(array $params, array $objects): array;
}