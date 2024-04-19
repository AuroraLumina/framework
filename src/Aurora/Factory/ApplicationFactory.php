<?php

namespace AuroraLumina\Factory;

use AuroraLumina\Application;

class ApplicationFactory
{

    public static function createApplication(): Application
    {
        return new Application();
    }
}