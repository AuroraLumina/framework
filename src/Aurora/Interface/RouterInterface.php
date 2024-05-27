<?php

namespace AuroraLumina\Interface;

interface RouterInterface
{
    /**
     * Add a POST route to the application.
     *
     * @param string $path  The route path
     * @param mixed $action The route action
     * @return void
     */
    public function post(string $path, mixed $action): void;

    /**
     * Add a GET route to the application.
     *
     * @param string $path  The route path
     * @param mixed $action The route action
     * @return void
     */
    public function get(string $path, mixed $action): void;
}