<?php

namespace Test\Adapter;

use Test\TestCase;

/**
 * ImapAdapter test case.
 */
class ImapAdapterTest extends TestCase
{
    /**
     * @var ImapAdapter
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Adapter\ImapAdapter')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Adapter\ImapAdapter::retrieve
     * @todo   Implement testRetrieve().
     */
    public function testRetrieve()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\ImapAdapter::parse
     * @todo   Implement testParse().
     */
    public function testParse()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
