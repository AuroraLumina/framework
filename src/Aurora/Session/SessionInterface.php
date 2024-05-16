<?php

namespace AuroraLumina\Session;

interface SessionInterface
{
    public static function insertSession(string $name, mixed $value): bool;

    public static function removeSession(string $name): void;

    public static function dropSessions(): void;
}