<?php

namespace AuroraLumina\Session;

interface SessionInterface
{
    public function insertSession(string $name, mixed $value): bool;

    public function removeSession(string $name): void;

    public function dropSessions(): void;

    public function getSession(string $key): mixed;
}