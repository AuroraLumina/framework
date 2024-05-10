<?php

namespace AuroraLumina\Interface;

use Psr\Http\Server\RequestHandlerInterface;

interface MiddlewareDispatcherInterface extends RequestHandlerInterface
{
    public function add($middleware): self;
}