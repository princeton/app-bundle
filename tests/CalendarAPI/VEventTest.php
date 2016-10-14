<?php

namespace TestCalendarAPI;

use DateTime;
use DateTimeZone;
use Test\TestCase;
use Princeton\App\CalendarAPI\RRule;
use Princeton\App\CalendarAPI\VEvent;

/**
 * VEvent test case.
 */
class VEventTest extends TestCase
{
    /**
     * @var VEvent
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();

        $this->object = new VEvent();
    }

    /**
     * @covers Princeton\App\CalendarAPI\VEvent
     * @covers Princeton\App\CalendarAPI\RFC2445
     */
    public function test__toString()
    {
        $expected = "BEGIN:VEVENT\r
UID:test-uid\r
SUMMARY:Sample\r
LOCATION:Test Location\r
URL:http://example.com/\r
DESCRIPTION:Lorem Ipsum Lorem Ipsum Lorem Ipsum escape:'    \\n\\;\\,\\\\' Lore\r
 m Ipsum Lorem Ipsum Lorem Ipsum\r
TRANSP:OPAQUE\r
PRIORITY:5\r
CATEGORIES:A,B\\, not C\r
DTSTAMP:20100102T085000Z\r
DTSTART:20100102T085000Z\r
DTEND:20100102T085000Z\r
RRULE:FREQ=MONTHLY;BYDAY=1SA;UNTIL=20101202T085000Z\r
EXDATE:20100203T085000Z\r
BEGIN:VALARM\r
TRIGGER:-PT15M\r
ACTION:DISPLAY\r
DESCRIPTION:Reminder\r
END:VALARM\r
END:VEVENT\r
";
        $tz = new DateTimeZone('America/New_York');

        // First-Saturday-of-the-month RRule.
        $rrule = new RRule();
        $rrule->freq = RRule::FREQ_MONTHLY;
        $rrule->byDay = '1' . RRule::SATURDAY;
        $rrule->until = new DateTime('2010-12-02T03:50', $tz);
        
        $this->object->uid = 'test-uid';
        $this->object->dtstamp = new DateTime('2010-01-02T03:50', $tz);
        $this->object->dtstart = new DateTime('2010-01-02T03:50', $tz);
        $this->object->dtend = new DateTime('2010-01-02T03:50', $tz);
        $this->object->summary = 'Sample';
        $this->object->priority = 5;
        $this->object->location = 'Test Location';
        $this->object->url = 'http://example.com/';
        $this->object->description = "Lorem Ipsum Lorem Ipsum Lorem Ipsum escape:'\t\r\n;,\\' Lorem Ipsum Lorem Ipsum Lorem Ipsum";
        $this->object->rrule = $rrule;
        $this->object->exdate[] = new DateTime('2010-02-03T03:50', $tz);
        // This is two categories, one with an embedded comma.
        $this->object->categories = ['A', 'B, not C'];
        $this->object->reminder = '-PT15M';

        $actual = $this->object->__toString();
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers Princeton\App\CalendarAPI\VEvent::formatStream
     */
    public function testFormatStream()
    {
        $expected = "BEGIN:VCALENDAR\r
PRODID:test-prodid\r
VERSION:1.1\r
METHOD:PUBLISH\r
X-CALSTART:20000101T050000Z\r
X-WR-CALNAME:Test Cal\r
BEGIN:VEVENT\r
UID:test-uid\r
SUMMARY:Sample\r
TRANSP:OPAQUE\r
DTSTAMP:20100102T085000Z\r
DTSTART:20100102T085000Z\r
DTEND:20100102T085000Z\r
END:VEVENT\r
END:VCALENDAR\r
";
        $tz = new DateTimeZone('America/New_York');
        $this->object->uid = 'test-uid';
        $this->object->dtstamp = new DateTime('2010-01-02T03:50', $tz);
        $this->object->dtstart = new DateTime('2010-01-02T03:50', $tz);
        $this->object->dtend = new DateTime('2010-01-02T03:50', $tz);
        $this->object->summary = 'Sample';

        $actual = $this->object->formatStream('Test Cal', 'test-prodid', new \DateTime('2000-01-01', $tz), '1.1');
        $this->assertSame($expected, $actual);
    }
}
