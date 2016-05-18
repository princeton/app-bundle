<?php

namespace Test\Adapter;

use Test\TestCase;

/**
 * LdapAdapter test case.
 */
class LdapAdapterTest extends TestCase
{
    /**
     * @var LdapAdapter
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Adapter\LdapAdapter')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Adapter\LdapAdapter::retrieve
     * @todo   Implement testRetrieve().
     */
    public function testRetrieve()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\LdapAdapter::parse
     * @todo   Implement testParse().
     */
    public function testParse()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Adapter\LdapAdapter::flatten
     * @todo   Implement testFlatten().
     */
    public function testFlatten()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
