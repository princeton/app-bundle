<?php

namespace Test\Adapter;

use Test\TestCase;

/**
 * ICalAdapter test case.
 */
class ICalAdapterTest extends TestCase
{
    /**
     * @var ICalAdapter
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Adapter\ICalAdapter')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Adapter\ICalAdapter::parse
     * @todo   Implement testParse().
     */
    public function testParse()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
