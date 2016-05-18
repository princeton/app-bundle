<?php

namespace Test\Adapter;

use Test\TestCase;

/**
 * BaseAdapter test case.
 */
class BaseAdapterTest extends TestCase
{
    /**
     * @var BaseAdapter
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Adapter\BaseAdapter')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Adapter\BaseAdapter::param
     * @todo   Implement testParam().
     */
    public function testParam()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\BaseAdapter::setParam
     * @todo   Implement testSetParam().
     */
    public function testSetParam()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\BaseAdapter::hasParam
     * @todo   Implement testHasParam().
     */
    public function testHasParam()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\BaseAdapter::registerParam
     * @todo   Implement testRegisterParam().
     */
    public function testRegisterParam()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\BaseAdapter::paramNames
     * @todo   Implement testParamNames().
     */
    public function testParamNames()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\BaseAdapter::perform
     * @todo   Implement testPerform().
     */
    public function testPerform()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\BaseAdapter::getInstance
     * @todo   Implement testGetInstance().
     */
    public function testGetInstance()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
