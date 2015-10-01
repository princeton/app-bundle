<?php

namespace Test\Strings;

use Test\TestCase;

/**
 * Internationalizer test case.
 */
class PositionalTest extends TestCase {

    /**
     * Tests Internationalizer::get()
     */
    public function testGet()
    {
        $result = 'test one two three test';
        
    	/* @var $stub \Princeton\App\Strings\Internationalizer */
    	$stub = $this->getMockForAbstractClass('Princeton\App\Strings\Internationalizer\Positional', array('en_US'));
        
    	$this->assertEquals($result, $stub->get('positional.none'));
        $this->assertEquals($result, $stub->get('positional.one', 'one'));
        $this->assertEquals($result, $stub->get('positional.two', 'one', 'two'));
        $this->assertEquals($result, $stub->get('positional.two', ['one', 'two']));
    }
}