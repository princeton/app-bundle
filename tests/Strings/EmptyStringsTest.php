<?php

namespace Test\Strings;

use Test\TestCase;

/**
 * EmptyStrings test case.
 */
class EmptyStringsTest extends TestCase
{
    /**
     * @var EmptyStrings
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        // $this->object = $this->getMockBuilder('Princeton\App\Strings\EmptyStrings')->setConstructorArgs([])->setMethods(null)->getMock();
    }

    public function testNothing()
    {
    }
}
