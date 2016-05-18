<?php

namespace Test\Formatter;

use Test\TestCase;

/**
 * XMLFormatter test case.
 */
class XMLFormatterTest extends TestCase
{
    /**
     * @var XMLFormatter
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Formatter\XMLFormatter')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Formatter\XMLFormatter::format
     * @todo   Implement testFormat().
     */
    public function testFormat()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Formatter\XMLFormatter::error
     * @todo   Implement testError().
     */
    public function testError()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
