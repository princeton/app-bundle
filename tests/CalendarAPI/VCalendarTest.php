<?php

namespace TestCalendarAPI;

use DateTime;
use Test\TestCase;
use Princeton\App\CalendarAPI\VCalendar;

/**
 * VCalendar test case.
 */
class VCalendarTest extends TestCase
{
    /**
     * @covers Princeton\App\CalendarAPI\VCalendar::__toString
     */
    public function test__toString()
    {
        $expected = "BEGIN:VCALENDAR\r
VERSION:2.0\r
METHOD:PUBLISH\r
PRODID:-a-test-prod-id-\r
X-CALSTART:20000101T050000Z\r
X-WR-CALNAME:My name is\\, test\r
END:VCALENDAR\r
";
        $subject = new VCalendar('My name is, test', '-a-test-prod-id-', new DateTime('2000-01-01'));
        
        $actual = $subject->__toString();
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers Princeton\App\CalendarAPI\VCalendar::addEvent
     * @todo   Implement testAddEvent().
     */
    public function testAddEvent()
    {
        // TODO
    }
}
