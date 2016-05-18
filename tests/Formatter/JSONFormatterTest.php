<?php

namespace Test\Formatter;

use Test\TestCase;

/**
 * JSONFormatter test case.
 */
class JSONFormatterTest extends TestCase
{
    /**
     * @var JSONFormatter
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Formatter\JSONFormatter')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Formatter\JSONFormatter::format
     * @todo   Implement testFormat().
     */
    public function testFormat()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Formatter\JSONFormatter::error
     * @todo   Implement testError().
     */
    public function testError()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
