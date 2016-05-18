<?php

namespace Test\Authentication;

use Test\TestCase;

/**
 * LDAPAuthenticator test case.
 */
class LDAPAuthenticatorTest extends TestCase
{
    /**
     * @var LDAPAuthenticator
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Authentication\LDAPAuthenticator')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Authentication\LDAPAuthenticator::isAuthenticated
     * @todo   Implement testIsAuthenticated().
     */
    public function testIsAuthenticated()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\LDAPAuthenticator::authenticate
     * @todo   Implement testAuthenticate().
     */
    public function testAuthenticate()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\LDAPAuthenticator::getAppConfig
     * @todo   Implement testGetAppConfig().
     */
    public function testGetAppConfig()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
