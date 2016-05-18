<?php

namespace Test\Adapter;

use Test\TestCase;

/**
 * ICal test case.
 */
class ICalTest extends TestCase
{
    /**
     * @var ICal
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Adapter\ICal')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Adapter\ICal::unescapeIcalText
     * @todo   Implement testUnescapeIcalText().
     */
    public function testUnescapeIcalText()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\ICal::addCalendarComponentWithKeyAndValue
     * @todo   Implement testAddCalendarComponentWithKeyAndValue().
     */
    public function testAddCalendarComponentWithKeyAndValue()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\ICal::keyValueFromString
     * @todo   Implement testKeyValueFromString().
     */
    public function testKeyValueFromString()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\ICal::iCalDateToUnixTimestamp
     * @todo   Implement testICalDateToUnixTimestamp().
     */
    public function testICalDateToUnixTimestamp()
    {
        $this->markTestIncomplete('Unimplemented test.');
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
