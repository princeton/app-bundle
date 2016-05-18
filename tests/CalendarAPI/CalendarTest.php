<?php

namespace Test\CalendarAPI;

use Test\TestCase;

/**
 * Calendar test case.
 */
class CalendarTest extends TestCase
{
    /**
     * @var Calendar
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\CalendarAPI\Calendar')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
