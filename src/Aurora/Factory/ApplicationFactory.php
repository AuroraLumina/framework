<?php

namespace AuroraLumina\Factory;

use AuroraLumina\Container;
use AuroraLumina\Application;

class ApplicationFactory
{
    /**
     * The application container.
     *
     * @var Container
     */
    protected static ?Container $container = null;

    /**
     * Create a new Application.
     *
     * @param  ?Container  $container
     * 
     * @return Application
     */
    public static function createApplication(
        ?Container $container = null
    ): Application
    {
        return new Application(
            $container ?? static::$container
        );
    }

    /**
     * Define an container in the application factory
     *
     * @param  Container $container
     * 
     * @return void
     */
    public static function setContainer(Container $container): void
    {
        static::$container = $container;
    }
}