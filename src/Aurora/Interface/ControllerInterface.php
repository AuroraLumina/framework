<?php

namespace AuroraLumina\Interface;

use AuroraLumina\Http\Response\Response;

interface ControllerInterface
{
    /**
     * Create a new response with the given body content.
     *
     * @param string $body Content of the response.
     * @return Response
     */
    public static function response(string $write): Response;
}