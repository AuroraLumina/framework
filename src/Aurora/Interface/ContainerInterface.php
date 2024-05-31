<?php

namespace AuroraLumina\Interface;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Get an instance from an id
     *
     * @param  string $service
     * @return string|ServiceInterface
     *
     * @throws Exception
     */
    public function get(string $service): string|ServiceInterface;

    /**
     * Check if you have an instance.
     *
     * @param  string $service
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
     * @param ServiceInterface $service
     * @return void
     *
     * @throws RuntimeException
     */
    public function bind(string|ServiceInterface $service): void;

    /**
     * Validates a service instance to ensure it implements the ServiceInterface.
     *
     * @param mixed $instance The service instance to validate.
     *
     * @return void
     *
     * @throws RuntimeException If the service instance does not implement ServiceInterface.
     */
    public function validateService(mixed $instance): void;
}