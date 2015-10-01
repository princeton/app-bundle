<?php

namespace Test\Strings;

use Test\TestCase;

/**
 * Internationalizer test case.
 */
class OrdinalTest extends TestCase {

    /**
     * Tests Internationalizer::get()
     */
    public function testGet()
    {
        $result = 'test one two three test';
        
    	/* @var $stub \Princeton\App\Strings\Internationalizer */
    	$stub = $this->getMockForAbstractClass('Princeton\App\Strings\Internationalizer\Ordinal', array('en_US'));
        
    	$this->assertEquals($result, $stub->get('ordinal.none'));
        $this->assertEquals($result, $stub->get('ordinal.one', 'one'));
        $this->assertEquals($result, $stub->get('ordinal.two', 'one', 'two'));
        $this->assertEquals($result, $stub->get('ordinal.two', ['one', 'two']));
        $this->assertEquals($result, $stub->get('ordinal.ten', ['one', '', '', '', '', '', '', '', '', 'two']));
        $this->assertEquals($result, $stub->get('ordinal.reverse', 'three', 'two', 'one'));
        $this->assertEquals($result, $stub->get('ordinal.reverse', ['three', 'two', 'one']));
    }
}