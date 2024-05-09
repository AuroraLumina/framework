<?php

namespace AuroraLumina;

use Closure;
use AuroraLumina\Interface\Service;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * The container records.
     *
     * @var array<Closure>
     */
    protected $instances = [];

    /**
     * Get an instance from an id
     *
     * @param  string  $id
     * @return Closure
     *
     * @throws \Exception
     */
    public function get(string $id): Service | Closure
    {
        if (!$this->has($id))
        {
            throw new \Exception("Container has not found: $id");
        }

        return $this->instances[$id];
    }

    /**
     * Check if you have an instance.
     *
     * @param  string  $id
     * @return void
     *
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->instances);
    }

    /**
     * Bind an instance from an id
     *
     * @param  string  $id
     * @return Closure
     *
     * @throws \Exception
     */
    public function bind(string $id, Service | Closure $concrete): void
    {
        if ($this->has($id))
        {
            throw new \Exception("Container has not found: $id");
        }

        $this->instances[$id] = $concrete;
    }
}