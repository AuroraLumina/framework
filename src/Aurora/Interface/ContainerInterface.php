<?php

namespace AuroraLumina\Interface;

interface ContainerInterface
{
    
    /**
     * Binds a service to the container.
     *
     * @param ServiceInterface $service The service to be bound.
     * @return void
     */
    public function bind(ServiceInterface $service): void;
    
    /**
     * Binds a scoped service to the container.
     *
     * @param string $service The service to be bound.
     * @return void
     */
    public function bindScoped(string $service): void;
}