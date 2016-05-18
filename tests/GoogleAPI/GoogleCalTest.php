<?php

namespace Test\GoogleAPI;

use Test\TestCase;

/**
 * GoogleCal test case.
 */
class GoogleCalTest extends TestCase
{
    /**
     * @var GoogleCal
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\GoogleAPI\GoogleCal')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\GoogleAPI\GoogleCal::authorize
     * @todo   Implement testAuthorize().
     */
    public function testAuthorize()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\GoogleAPI\GoogleCal::checkToken
     * @todo   Implement testCheckToken().
     */
    public function testCheckToken()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\GoogleAPI\GoogleCal::insertEvent
     * @todo   Implement testInsertEvent().
     */
    public function testInsertEvent()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\GoogleAPI\GoogleCal::updateEvent
     * @todo   Implement testUpdateEvent().
     */
    public function testUpdateEvent()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\GoogleAPI\GoogleCal::deleteEvent
     * @todo   Implement testDeleteEvent().
     */
    public function testDeleteEvent()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
