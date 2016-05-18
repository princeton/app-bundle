<?php

namespace Test\Authentication;

use Test\TestCase;

/**
 * CASAuthenticator test case.
 */
class CASAuthenticatorTest extends TestCase
{
    /**
     * @var CASAuthenticator
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Authentication\CASAuthenticator')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Authentication\CASAuthenticator::prepare
     * @todo   Implement testPrepare().
     */
    public function testPrepare()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\CASAuthenticator::isAuthenticated
     * @todo   Implement testIsAuthenticated().
     */
    public function testIsAuthenticated()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\CASAuthenticator::authenticate
     * @todo   Implement testAuthenticate().
     */
    public function testAuthenticate()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\CASAuthenticator::logoff
     * @todo   Implement testLogoff().
     */
    public function testLogoff()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\CASAuthenticator::getAppConfig
     * @todo   Implement testGetAppConfig().
     */
    public function testGetAppConfig()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
