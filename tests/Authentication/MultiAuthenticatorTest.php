<?php

namespace Test\Authentication;

use Test\TestCase;

/**
 * MultiAuthenticator test case.
 */
class MultiAuthenticatorTest extends TestCase
{
    /**
     * @var MultiAuthenticator
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Authentication\MultiAuthenticator')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Authentication\MultiAuthenticator::isAuthenticated
     * @todo   Implement testIsAuthenticated().
     */
    public function testIsAuthenticated()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\MultiAuthenticator::authenticate
     * @todo   Implement testAuthenticate().
     */
    public function testAuthenticate()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\MultiAuthenticator::getAppConfig
     * @todo   Implement testGetAppConfig().
     */
    public function testGetAppConfig()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
