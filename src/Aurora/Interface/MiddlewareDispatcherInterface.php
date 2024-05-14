<?php

namespace AuroraLumina\Interface;

use Psr\Http\Server\RequestHandlerInterface;

/**
 * Interface MiddlewareDispatcherInterface
 *
 * Defines the contract for a middleware dispatcher, which is capable of handling
 * middleware stacks and dispatching requests through them.
 */
interface MiddlewareDispatcherInterface extends RequestHandlerInterface
{

    /**
     * Adds a middleware to the dispatcher.
     *
     * @param mixed $middleware The middleware to add.
     * @return MiddlewareDispatcherInterface This instance for method chaining.
     */
    public function add($middleware): self;
}
