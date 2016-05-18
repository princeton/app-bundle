<?php

namespace Test\Scheduler;

use Test\TestCase;

/**
 * Scheduler test case.
 */
class SchedulerTest extends TestCase
{
    /**
     * @var Scheduler
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Scheduler\Scheduler')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Scheduler\Scheduler::run
     * @todo   Implement testRun().
     */
    public function testRun()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Scheduler\Scheduler::setTimeStep
     * @todo   Implement testSetTimeStep().
     */
    public function testSetTimeStep()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Scheduler\Scheduler::setTimeOffset
     * @todo   Implement testSetTimeOffset().
     */
    public function testSetTimeOffset()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
