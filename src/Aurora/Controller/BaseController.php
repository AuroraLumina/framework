<?php

namespace AuroraLumina\Controller;

use AuroraLumina\Http\Response\Response;
use AuroraLumina\Interface\ControllerInterface;

abstract class BaseController implements ControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function response(object|array|string $body, $flag = JSON_PRETTY_PRINT): Response
    {
        $response = new Response;

        if (is_string($body))
        {
            $response->getBody()->write($body);
        }
        else
        {
            $response->getBody()->write(json_encode($body, $flag));
        }

        return $response;
    }
}