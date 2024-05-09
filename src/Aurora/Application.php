<?php

namespace AuroraLumina;

class Application
{
    /**
     * The application container.
     *
     * @var Container
     */
    protected ?Container $container;

    /**
     * Create a new Application instance.
     *
     * @param  ?Container  $container
     * 
     * @return void
     */
    public function __construct(
        ?Container $container = null
    )
    {
        $this->container = $container;
    }
}