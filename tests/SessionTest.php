<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use AuroraLumina\Session\SessionManager;

class SessionTest extends TestCase
{
    /**
     * Test session insertion and retrieval.
     *
     * This method tests the insertion and retrieval of a session value using the SessionManager class.
     */
    public function testSession(): void
    {
        $sessionManager = new SessionManager();
        $sessionManager->insertSession('key', 'value');
        $this->assertEquals('value', $sessionManager->getSession('key'));
    }

    public function testInsertSession_NewKey()
    {
        $sessionManager = new SessionManager();
        $key = 'test_key';
        $value = 'test_value';

        $this->assertTrue($sessionManager->insertSession($key, $value));
        $this->assertEquals($value, $sessionManager->getSession($key));
    }

    public function testInsertSession_EmptyStringValue()
    {
        $sessionManager = new SessionManager();
        $key = 'test_key';

        $this->assertTrue($sessionManager->insertSession($key, ''));
        $this->assertEquals('', $sessionManager->getSession($key));
    }
}