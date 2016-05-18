<?php

namespace Test\Strings;

use Test\TestCase;

/**
 * NoopStrings test case.
 */
class NoopStringsTest extends TestCase
{
    /**
     * @var NoopStrings
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Strings\NoopStrings')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Strings\NoopStrings::get
     * @todo   Implement testGet().
     */
    public function testGet()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Strings\NoopStrings::getLanguage
     * @todo   Implement testGetLanguage().
     */
    public function testGetLanguage()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Strings\NoopStrings::getMapping
     * @todo   Implement testGetMapping().
     */
    public function testGetMapping()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Strings\NoopStrings::setLanguage
     * @todo   Implement testSetLanguage().
     */
    public function testSetLanguage()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
