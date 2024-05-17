<?php

namespace AuroraLumina\Controller;

use AuroraLumina\Http\Response\Response;
use AuroraLumina\Interface\ControllerInterface;

abstract class BaseController implements ControllerInterface
{
    /**
     * Create a new response with the given body content.
     *
     * @param string $body Content of the response.
     * @return Response
     */
    protected static function response(string $write): Response
    {
        $response = new Response();
        $response->getBody()->write($write);
        return $response;
    }
}