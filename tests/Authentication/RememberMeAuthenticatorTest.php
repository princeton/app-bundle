<?php

namespace Test\Authentication;

use Test\TestCase;

/**
 * RememberMeAuthenticator test case.
 */
class RememberMeAuthenticatorTest extends TestCase
{
    /**
     * @var RememberMeAuthenticator
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Authentication\RememberMeAuthenticator')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Authentication\RememberMeAuthenticator::setDelegate
     * @todo   Implement testSetDelegate().
     */
    public function testSetDelegate()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\RememberMeAuthenticator::isAuthenticated
     * @todo   Implement testIsAuthenticated().
     */
    public function testIsAuthenticated()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\RememberMeAuthenticator::authenticate
     * @todo   Implement testAuthenticate().
     */
    public function testAuthenticate()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\RememberMeAuthenticator::afterAuthenticated
     * @todo   Implement testAfterAuthenticated().
     */
    public function testAfterAuthenticated()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\RememberMeAuthenticator::configureDeviceUser
     * @todo   Implement testConfigureDeviceUser().
     */
    public function testConfigureDeviceUser()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authentication\RememberMeAuthenticator::getAppConfig
     * @todo   Implement testGetAppConfig().
     */
    public function testGetAppConfig()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
