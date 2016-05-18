<?php

namespace Test\Injection;

use Test\TestCase;

/**
 * DependencyManager test case.
 */
class DependencyManagerTest extends TestCase
{
    /**
     * @var DependencyManager
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Injection\DependencyManager')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Injection\DependencyManager::register
     * @todo   Implement testRegister().
     */
    public function testRegister()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Injection\DependencyManager::get
     * @todo   Implement testGet().
     */
    public function testGet()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Injection\DependencyManager::inject
     * @todo   Implement testInject().
     */
    public function testInject()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
