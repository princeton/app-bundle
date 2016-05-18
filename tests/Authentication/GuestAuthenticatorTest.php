<?php

namespace Test\Authentication;

use Test\TestCase;

/**
 * GuestAuthenticator test case.
 */
class GuestAuthenticatorTest extends TestCase
{
    /**
     * @var GuestAuthenticator
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Authentication\GuestAuthenticator')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Authentication\GuestAuthenticator::isAuthenticated
     * @todo   Implement testIsAuthenticated().
     */
    public function testIsAuthenticated()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\GuestAuthenticator::authenticate
     * @todo   Implement testAuthenticate().
     */
    public function testAuthenticate()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\GuestAuthenticator::getAppConfig
     * @todo   Implement testGetAppConfig().
     */
    public function testGetAppConfig()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
