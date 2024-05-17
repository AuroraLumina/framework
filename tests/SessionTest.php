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
}