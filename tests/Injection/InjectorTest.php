<?php

namespace Test\Injection;

use Test\TestCase;

/**
 * Injector test case.
 */
class InjectorTest extends TestCase
{
    /**
     * @var Injector
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Injection\Injector')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Injection\Injector::getInjected
     * @todo   Implement testGetInjected().
     */
    public function testGetInjected()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Injection\Injector::setManager
     * @todo   Implement testSetManager().
     */
    public function testSetManager()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
