<?php

namespace Test\Authentication;

use Test\TestCase;
use Princeton\App\Authentication\AuthenticatorFactory;
use Princeton\App\Authentication\MultiAuthenticator;
use Princeton\App\Config\Configuration;

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
        $this->object = new MultiAuthenticator(
            $this->createMock(Configuration::class),
            $this->createMock(AuthenticatorFactory::class)
        );
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
