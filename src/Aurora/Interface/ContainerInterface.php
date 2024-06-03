<?php

namespace AuroraLumina\Interface;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Get an instance from an id
     *
     * @param  string $service
     * 
     * @return mixed
     *
     * @throws Exception
     */
    public function get(string $service): mixed;

    /**
     * Check if you have an instance.
     *
     * @param  string $service
     * 
     * @return void
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
     * Bind an instance from an service
     *
     * @param object $service
     * 
     * @return void
     *
     * @throws Exception
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
     * @param array $params The constructor parameters.
     * @return array The resolved dependencies.
     */
    public function resolveConstructorDependencies(array $params): array;
}