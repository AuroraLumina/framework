<?php

namespace AuroraLumina\Interface;

use AuroraLumina\Http\Response\Response;

/**
 * Interface ControllerInterface
 * @package AuroraLumina\Interface
 */
interface ControllerInterface
{
    /**
     * Create a new response with the given body content.
     *
     * @param object|array|string $body Content of the response.
     * @param int $flag Optional flags for JSON encoding.
     * @return Response
     */
    public static function response(object|array|string $body, int $flag = JSON_PRETTY_PRINT): Response;
}
