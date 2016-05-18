<?php

namespace Test\Formatter;

use Test\TestCase;

/**
 * ICalFormatter test case.
 */
class ICalFormatterTest extends TestCase
{
    /**
     * @var ICalFormatter
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Formatter\ICalFormatter')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Formatter\ICalFormatter::format
     * @todo   Implement testFormat().
     */
    public function testFormat()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Formatter\ICalFormatter::error
     * @todo   Implement testError().
     */
    public function testError()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
