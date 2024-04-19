<?php

namespace AuroraLumina\Factory;

use AuroraLumina\Application;
use AuroraLumina\Container;

class ApplicationFactory
{
    protected static ?Container $container = null;

    public static function createApplication(
        ?Container $container = null
    ): Application
    {
        return new Application(
            $container ?? static::$container
        );
    }

    public static function setContainer(Container $container): void
    {
        static::$container = $container;
    }
}