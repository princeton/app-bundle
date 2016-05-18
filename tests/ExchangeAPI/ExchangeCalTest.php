<?php

namespace Test\ExchangeAPI;

use Test\TestCase;

/**
 * ExchangeCal test case.
 */
class ExchangeCalTest extends TestCase
{
    /**
     * @var ExchangeCal
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\ExchangeAPI\ExchangeCal')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\ExchangeAPI\ExchangeCal::insertEvent
     * @todo   Implement testInsertEvent().
     */
    public function testInsertEvent()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\ExchangeAPI\ExchangeCal::updateEvent
     * @todo   Implement testUpdateEvent().
     */
    public function testUpdateEvent()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\ExchangeAPI\ExchangeCal::deleteEvent
     * @todo   Implement testDeleteEvent().
     */
    public function testDeleteEvent()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
