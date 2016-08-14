<?php

namespace Test\Adapter;

use Test\TestCase;
use Princeton\App\Adapter\ICal;

/**
 * ICal test case.
 */
class ICalTest extends TestCase
{
    protected static $sample = "BEGIN:VCALENDAR\r
METHOD:PUBLISH\r
VERSION:2.0\r
X-WR-CALNAME:Test Calendar\r
X-WR-TIMEZONE:America/New_York\r
CALSCALE:GREGORIAN\r
BEGIN:VEVENT\r
uid:36211\r
DTSTART:20150909\r
SUMMARY:Test\r
DESCRIPTION:test item with wrap\r
 ping description\r
END:VEVENT\r
END:VCALENDAR\r";

    /**
     * @covers Princeton\App\Adapter\ICal::__construct
     */
    public function testParsing()
    {
        $ical = new ICal(self::$sample);
        $events = $ical->events();
        $this->assertSame('test item with wrapping description', $events[0]['DESCRIPTION']);
    }

    /**
     * @covers Princeton\App\Adapter\ICal::unescapeIcalText
     */
    public function testUnescapeIcalText()
    {
        $input = "abcd\\efgh\\;\\,,\\\\xyz";
        $expected = "abcd\\efgh;,,\\xyz";
        $ical = new ICal('');
        $actual = $ical->unescapeIcalText($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers Princeton\App\Adapter\ICal::addCalendarComponentWithKeyAndValue
     */
    public function testAddCalendarComponentWithKeyAndValue()
    {
        $ical = new ICal(self::$sample);
        $ical->addCalendarComponentWithKeyAndValue('VEVENT', 'TEST', 'test');
        $ical->addCalendarComponentWithKeyAndValue('VTODO', 'TEST', 'test');
        $ical->addCalendarComponentWithKeyAndValue('VEVENT', false, 'test');
        $ical->addCalendarComponentWithKeyAndValue('VTODO', false, 'test');

        $input = " test 1 ,test2,tests3\\,4\\,5,other";
        $ical->addCalendarComponentWithKeyAndValue('VEVENT', 'CATEGORIES', $input);
        $events = $ical->events();
        $this->assertSame(['test 1', 'test2', 'tests3,4,5', 'other'], $events[0]['CATEGORIES']);
    }

    /**
     * @covers Princeton\App\Adapter\ICal::keyValueFromString
     */
    public function testKeyValueFromString()
    {
        $ical = new ICal('');
        $actual = $ical->keyValueFromString('KEY:VALUE');
        $this->assertSame(['KEY', 'VALUE'], $actual);

        $this->assertFalse($ical->keyValueFromString(':BAD-NO-KEY'));
        $this->assertFalse($ical->keyValueFromString('BAD-NO-SEMICOLON'));
    }

    /**
     * @covers Princeton\App\Adapter\ICal::iCalDateToUnixTimestamp
     * @todo   Implement testICalDateToUnixTimestamp().
     */
    public function testICalDateToUnixTimestamp()
    {
        $ical = new ICal('');
        $this->assertSame(1500000000, $ical->iCalDateToUnixTimestamp('20170713T224000Z'));
        $this->assertSame(1500000000, $ical->iCalDateToUnixTimestamp('20170713T224000'));
    }

    /**
     * @covers Princeton\App\Adapter\ICal::events
     * @todo   Implement testEvents().
     */
    public function testEvents()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\ICal::hasEvents
     * @todo   Implement testHasEvents().
     */
    public function testHasEvents()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\ICal::eventsFromRange
     * @todo   Implement testEventsFromRange().
     */
    public function testEventsFromRange()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\ICal::sortEventsWithOrder
     * @todo   Implement testSortEventsWithOrder().
     */
    public function testSortEventsWithOrder()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
