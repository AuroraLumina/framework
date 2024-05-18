<?php

namespace AuroraLumina\Logger;

use Psr\Log\LoggerTrait;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use AuroraLumina\Interface\ServiceInterface;
use Stringable;

class Logger extends AbstractLogger implements ServiceInterface, LoggerInterface
{
    use LoggerTrait;
    
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level   The log level.
     * @param string $message The log message.
     * @param array  $context The log context.
     *
     * @return void
     */
    public function log(mixed $level, Stringable|string $message, array $context = []): void
    {
        print sprintf('[%s] %s', strtoupper($level), $message) . PHP_EOL;

        if (!empty($context)) {
            print sprintf('Context: %s', json_encode($context)) . PHP_EOL;
        }
    }
}