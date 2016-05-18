<?php

namespace Test\Authorization;

use Test\TestCase;

/**
 * AllowAllAuthorizer test case.
 */
class AllowAllAuthorizerTest extends TestCase
{
    /**
     * @var AllowAllAuthorizer
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Authorization\AllowAllAuthorizer')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Princeton\App\Authorization\AllowAllAuthorizer::checkIfAll
     * @todo   Implement testCheckIfAll().
     */
    public function testCheckIfAll()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authorization\AllowAllAuthorizer::checkIfSome
     * @todo   Implement testCheckIfSome().
     */
    public function testCheckIfSome()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }

    /**
     * @covers Princeton\App\Authorization\AllowAllAuthorizer::checkIfSuper
     * @todo   Implement testCheckIfSuper().
     */
    public function testCheckIfSuper()
    {
        $this->markTestIncomplete('Unimplemented test.');
    }
}
