<?php

namespace AuroraLumina;

class Application
{
    public function __construct(
        ?Container $container = null
    )
    {
        $this->container = $container;
    }
}