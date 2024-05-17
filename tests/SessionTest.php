<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use AuroraLumina\Session\SessionManager;

class SessionTest extends TestCase
{
    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * Set up the session manager before each test.
     */
    protected function setUp(): void
    {
        $this->sessionManager = new SessionManager();
    }

    /**
     * Test session insertion and retrieval.
     *
     * This method tests the insertion and retrieval of a session value using the SessionManager class.
     */
    public function testInsertSession()
    {
        $this->assertTrue($this->sessionManager->insertSession('key', 'value'));
        $this->assertFalse($this->sessionManager->insertSession('key', 'value'));
        $this->assertEquals('value', $this->sessionManager->getSession('key'));
    }
    

    public function testRemoveSession()
    {
        $this->assertTrue($this->sessionManager->insertSession('key', 'value'));
        $this->sessionManager->removeSession('key');
        $this->assertNull($this->sessionManager->getSession('key'));
    }

    /**
     * Clear the session data after each test.
     */
    protected function tearDown(): void
    {
        $this->sessionManager->dropSessions();
    }
}