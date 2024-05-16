<?php

namespace AuroraLumina\Session;


session_start();

class SessionManager implements SessionInterface
{
    private const SESSION_PREFIX = 'AuroraLuminus';
    
    /**
     * Insert a session value
     * 
     * @param string $name name of the session
     * @param mixed $value value of the session
     * @return bool
     */
    public static function insertSession(string $key, mixed $value): bool
    {
        if (!self::hasSession(self::concatenateSessionKey($key)))
        {
            self::putSession(self::concatenateSessionKey($key), $value);
            return true;
        }
        return false;
    }

    /**
     * Remove a session
     * 
     * @param string $name name of the session
     * @return void
     */
    public static function removeSession(string $key): void
    {
         if (self::hasSession(self::concatenateSessionKey($key)))
         {
            self::dropSession($key);
         }
    }

    /**
     * Drop all sessions
     * 
     * @return void
     */
    public static function dropSessions(): void
    {
        unset($_SESSION);
        $_SESSION = [];
    }

    /**
     * Check if has a session by key
     * 
     * @param string $name name of the session
     * @return bool
     */
    private static function hasSession(string $name): bool
    {
        return array_key_exists($name, $_SESSION);
    }

    /**
     * Put an value to a session
     * 
     * @param string $key The key of the session
     * @return void
     */
    private static function putSession(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Drop a session
     * 
     * @param string $key The key of the session
     * @return void
     */
    private static function dropSession(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Dump all sessions
     * 
     * @return void
     */
    public static function dump(): void
    {
        var_dump($_SESSION);
    }


    /**
     * Concat Session Key string
     * 
     * @param string $key The key of the session
     * @return string
     */
    private static function concatenateSessionKey(string $name): string
    {
        $prefix = self::SESSION_PREFIX;
        return "{$prefix}[{$name}]";
    }
}