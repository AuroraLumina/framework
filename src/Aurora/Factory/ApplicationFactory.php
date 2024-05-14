<?php

namespace AuroraLumina\Factory;

use AuroraLumina\Container;
use AuroraLumina\Application;
use AuroraLumina\Routing\Router;
use AuroraLumina\Middleware\MiddlewareDispatcher;

class ApplicationFactory
{
    /**
     * The application container.
     *
     * @var Container|null
     */
    protected static ?Container $container;

    /**
     * The application Router.
     *
     * @var Router|null
     */
    protected static ?Router $router;

    /**
     * The application MiddlewareDispatcher.
     *
     * @var MiddlewareDispatcher|null
     */
    protected static ?MiddlewareDispatcher $middlewareDispatcher;

    /**
     * Create a new Application.
     *
     * @param Container|null $container The dependency injection container.
     * 
     * @return Application The created application instance.
     */
    public static function createApplication(?Container $container = null): Application
    {
        static::$container = $container ?? static::$container ?? new Container();
        static::$router = new Router(static::$container);
        static::$middlewareDispatcher = new MiddlewareDispatcher(static::$router);
        return new Application(
            static::$container,
            static::$router,
            static::$middlewareDispatcher
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