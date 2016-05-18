<?php

namespace Test\Adapter;

use Test\TestCase;

/**
 * XmlAdapter test case.
 */
class XmlAdapterTest extends TestCase
{
    /**
     * @var XmlAdapter
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Adapter\XmlAdapter')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Adapter\XmlAdapter::parse
     * @todo   Implement testParse().
     */
    public function testParse()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
