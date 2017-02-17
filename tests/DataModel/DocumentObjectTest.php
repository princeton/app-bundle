<?php

namespace Test\DataModel;

use Test\TestCase;
use Princeton\App\DataModel\DocumentObject;

/**
 * DocumentObject test case.
 */
class DocumentObjectTest extends TestCase
{
    /**
     * @var DocumentObject
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\DataModel\DocumentObject')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\DataModel\DocumentObject::id
     * @todo   Implement testId().
     */
    public function testId()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\DataModel\DocumentObject::isActive
     * @todo   Implement testIsActive().
     */
    public function testIsActive()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\DataModel\DocumentObject::activate
     * @todo   Implement testActivate().
     */
    public function testActivate()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\DataModel\DocumentObject::deactivate
     * @todo   Implement testDeactivate().
     */
    public function testDeactivate()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\DataModel\DocumentObject::updateTimestamp
     * @todo   Implement testUpdateTimestamp().
     */
    public function testUpdateTimestamp()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\DataModel\DocumentObject::asArray
     * @todo   Implement testAsArray().
     */
    public function testAsArray()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\DataModel\DocumentObject::jsonSerialize
     * @todo   Implement testJsonSerialize().
     */
    public function testJsonSerialize()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\DataModel\DocumentObject::currentTimeMillis
     */
    public function testCurrentTimeMillis()
    {
        $now = time();
        $actual = DocumentObject::currentTimeMillis();
        $this->assertGreaterThan($now * 1000, $actual);
        $this->assertLessThan(($now + 1) * 1000, $actual);
    }

    /**
     * @covers Princeton\App\DataModel\DocumentObject::millisToDateTime
     */
    public function testMillisToDateTime()
    {
        /** @var $date \DateTime */
        $now = time();
        $date = DocumentObject::millisToDateTime($now * 1000);
        $this->assertSame(date_default_timezone_get(), $date->getTimezone()->getName());
        $this->assertSame($now, $date->getTimestamp());
        $this->assertSame(date('YmdHi', $now), $date->format('YmdHi'));
    }
}
