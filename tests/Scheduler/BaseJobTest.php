<?php

namespace Test\Scheduler;

use Test\TestCase;

/**
 * BaseJob test case.
 */
class BaseJobTest extends TestCase
{
    /**
     * @var BaseJob
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Scheduler\BaseJob')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Scheduler\BaseJob::name
     * @todo   Implement testName().
     */
    public function testName()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
