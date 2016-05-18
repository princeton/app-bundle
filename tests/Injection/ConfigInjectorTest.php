<?php

namespace Test\Injection;

use Test\TestCase;

/**
 * ConfigInjector test case.
 */
class ConfigInjectorTest extends TestCase
{
    /**
     * @var ConfigInjector
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Injection\ConfigInjector')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Injection\ConfigInjector::getAppConfig
     * @todo   Implement testGetAppConfig().
     */
    public function testGetAppConfig()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
