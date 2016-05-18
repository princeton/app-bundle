<?php

namespace Test\Scheduler;

use Test\TestCase;

/**
 * Worker test case.
 */
class WorkerTest extends TestCase
{
    /**
     * @var Worker
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Scheduler\Worker')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Scheduler\Worker::handleSignals
     * @todo   Implement testHandleSignals().
     */
    public function testHandleSignals()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
