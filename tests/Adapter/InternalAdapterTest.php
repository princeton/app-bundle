<?php

namespace Test\Adapter;

use Test\TestCase;

/**
 * InternalAdapter test case.
 */
class InternalAdapterTest extends TestCase
{
    /**
     * @var InternalAdapter
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Adapter\InternalAdapter')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Adapter\InternalAdapter::provide
     * @todo   Implement testProvide().
     */
    public function testProvide()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\InternalAdapter::parse
     * @todo   Implement testParse().
     */
    public function testParse()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\InternalAdapter::retrieve
     * @todo   Implement testRetrieve().
     */
    public function testRetrieve()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
