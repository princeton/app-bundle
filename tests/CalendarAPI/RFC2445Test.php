<?php

namespace TestCalendarAPI;

use Test\TestCase;
use Princeton\App\CalendarAPI\RFC2445;

/**
 * RFC2445 test case.
 */
class RFC2445Test extends TestCase
{
    /**
     * @var RFC2445
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();

        //$mock = $this->getMockBuilder(RFC2445::class)
            //->setConstructorArgs([])->setMethods(null)->getMock();

        //$this->object = $mock;
    }

    /**
     * @covers Princeton\App\CalendarAPI\RFC2445::format
     * @todo   Implement testFormat().
     */
    public function testFormat()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\CalendarAPI\RFC2445::dateStr
     * @todo   Implement testDateStr().
     */
    public function testDateStr()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
