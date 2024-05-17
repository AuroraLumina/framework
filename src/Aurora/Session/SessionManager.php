<?php

namespace AuroraLumina\Session;

use AuroraLumina\Interface\ServiceInterface;
use SessionHandlerInterface;

class SessionManager implements ServiceInterface, SessionInterface
{
    private const SESSION_PREFIX = 'AuroraLuminus_';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Insert a session value.
     * 
     * @param string $key Key of the session
     * @param mixed $value Value of the session
     * @return bool
     */
    public function insertSession(string $key, mixed $value): bool
    {
        $sessionKey = $this->concatenateSessionKey($key);
        if (!$this->hasSession($sessionKey))
        {
            $this->putSession($sessionKey, $value);
            return true;
        }
        return false;
    }
    
    /**
     * Remove a session.
     * 
     * @param string $key Key of the session
     * @return void
     */
    public function removeSession(string $key): void
    {
        $sessionKey = $this->concatenateSessionKey($key);
        if ($this->hasSession($sessionKey))
        {
            $this->dropSession($sessionKey);
        }
    }

    /**
     * Drop all sessions.
     * 
     * @return void
     */
    public function dropSessions(): void
    {
        session_destroy();
    }

    /**
     * Check if a session exists by key.
     * 
     * @param string $name Name of the session
     * @return bool
     */
    private function hasSession(string $name): bool
    {
        return array_key_exists($name, $_SESSION);
    }

    /**
     * Put a value into a session.
     * 
     * @param string $key The key of the session
     * @param mixed $value The value to be stored
     * @return void
     */
    private function putSession(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Drop a session.
     * 
     * @param string $key The key of the session
     * @return void
     */
    private function dropSession(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Dump all sessions for debugging.
     * 
     * @return void
     */
    protected function dump(): void
    {
        var_dump($_SESSION);
    }

    /**
     * Concatenate Session Key string.
     * 
     * @param string $name The key of the session
     * @return string
     */
    private function concatenateSessionKey(string $name): string
    {
        return self::SESSION_PREFIX . $name;
    }

    /**
     * Retrieve a session value.
     * 
     * @param string $key The key of the session
     * @return mixed
     */
    public function getSession(string $key): mixed
    {
        $sessionKey = $this->concatenateSessionKey($key);
        return $this->hasSession($sessionKey) ? $_SESSION[$sessionKey] : null;
    }

    /**
     * Regenerate session ID.
     * 
     * @param bool $deleteOldSession Whether to delete the old session
     * @return bool
     */
    public function regenerateSessionId(bool $deleteOldSession = false): bool
    {
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Destroy the session.
     * 
     * @return bool
     */
    public function destroySession(): bool
    {
        if (session_id() !== '' || isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        return session_destroy();
    }

    /**
     * Set session handler.
     * 
     * @param SessionHandlerInterface $handler Custom session handler
     * @return bool
     */
    public function setSessionHandler(SessionHandlerInterface $handler): bool
    {
        return session_set_save_handler($handler, true);
    }
}
