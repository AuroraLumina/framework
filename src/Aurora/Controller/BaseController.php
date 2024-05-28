<?php

namespace AuroraLumina\Controller;

use AuroraLumina\Http\Response\Response;
use AuroraLumina\Interface\ControllerInterface;

abstract class BaseController implements ControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function response(string $write): Response
    {
        $response = new Response();
        $response->getBody()->write($write);
        return $response;
    }
}