<?php

namespace Test\Reports;

use Test\TestCase;

/**
 * Aggregator test case.
 */
class AggregatorTest extends TestCase
{
    /**
     * @var Aggregator
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Reports\Aggregator')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Reports\Aggregator::log
     * @todo   Implement testLog().
     */
    public function testLog()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
