<?php

namespace Test\Reports;

use Test\TestCase;

/**
 * DailyAggregates test case.
 */
class DailyAggregatesTest extends TestCase
{
    /**
     * @var DailyAggregates
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Reports\DailyAggregates')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Reports\DailyAggregates::log
     * @todo   Implement testLog().
     */
    public function testLog()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Reports\DailyAggregates::getAppConfig
     * @todo   Implement testGetAppConfig().
     */
    public function testGetAppConfig()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
