<?php

namespace Test\Formatter;

use Test\TestCase;

/**
 * Formatter test case.
 */
class FormatterTest extends TestCase
{
    /**
     * @var Formatter
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Formatter\Formatter')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Formatter\Formatter::getFormatter
     * @todo   Implement testGetFormatter().
     */
    public function testGetFormatter()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
