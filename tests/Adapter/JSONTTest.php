<?php

namespace Test\Adapter;

use Test\TestCase;

/**
 * JSONT test case.
 */
class JSONTTest extends TestCase
{
    /**
     * @var JSONT
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Adapter\JSONT')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Adapter\JSONT::transform
     * @todo   Implement testTransform().
     */
    public function testTransform()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\JSONT::apply
     * @todo   Implement testApply().
     */
    public function testApply()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\JSONT::processArg
     * @todo   Implement testProcessArg().
     */
    public function testProcessArg()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\JSONT::evaluate
     * @todo   Implement testEvaluate().
     */
    public function testEvaluate()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\JSONT::expand
     * @todo   Implement testExpand().
     */
    public function testExpand()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
