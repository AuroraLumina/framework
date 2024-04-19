<?php

namespace AuroraLumina;

use Closure;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected $instances = [];

    public function get(string $id): Closure
    {
        if (!$this->has($id))
        {
            throw new \Exception("Container has not found: $id");
        }

        return $this->instances[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->instances);
    }

    public function bind(string $id, Closure $concrete): void
    {
        if ($this->has($id))
        {
            throw new \Exception("Container has not found: $id");
        }

        $this->instances[$id] = $concrete;
    }
}