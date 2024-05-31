<?php

namespace AuroraLumina\Factory;

use AuroraLumina\Container;
use AuroraLumina\Application;
use AuroraLumina\Request\RouterRequest;
use AuroraLumina\Interface\ContainerInterface;
use AuroraLumina\Middleware\MiddlewareDispatcher;
use AuroraLumina\Interface\RouterRequestInterface;
use AuroraLumina\Interface\MiddlewareDispatcherInterface;

class ApplicationFactory
{
    /**
     * The application container.
     *
     * @var ContainerInterface|null
     */
    protected static ?ContainerInterface $container;

    /**
     * The application Router.
     *
     * @var RouterRequestInterface|null
     */
    protected static ?RouterRequestInterface $router;

    /**
     * The application MiddlewareDispatcher.
     *
     * @var MiddlewareDispatcherInterface|null
     */
    protected static ?MiddlewareDispatcherInterface $middlewareDispatcher;

    /**
     * Create a new Application.
     *
     * @param ContainerInterface|null $container The dependency injection container.
     * 
     * @return Application The created application instance.
     */
    public static function createApplication(?ContainerInterface $container = null): Application
    {
        static::$container = $container ?? static::$container ?? new Container();
        static::$router = new RouterRequest(static::$container);
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
     * @param  ContainerInterface $container
     * 
     * @return void
     */
    public static function setContainer(ContainerInterface $container): void
    {
        static::$container = $container;
    }
}