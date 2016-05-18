<?php

namespace Test\Reports;

use Test\TestCase;

/**
 * MonthlyAggregates test case.
 */
class MonthlyAggregatesTest extends TestCase
{
    /**
     * @var MonthlyAggregates
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Reports\MonthlyAggregates')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Reports\MonthlyAggregates::log
     * @todo   Implement testLog().
     */
    public function testLog()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
