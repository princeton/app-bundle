<?php

namespace Test\Formatter;

use Test\TestCase;

/**
 * XHTMLFormatter test case.
 */
class XHTMLFormatterTest extends TestCase
{
    /**
     * @var XHTMLFormatter
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Formatter\XHTMLFormatter')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Formatter\XHTMLFormatter::format
     * @todo   Implement testFormat().
     */
    public function testFormat()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Formatter\XHTMLFormatter::error
     * @todo   Implement testError().
     */
    public function testError()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
