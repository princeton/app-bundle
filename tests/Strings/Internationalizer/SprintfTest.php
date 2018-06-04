<?php

namespace Test\Strings;

use Test\TestCase;
use Princeton\App\Cache\Cache;

/**
 * Internationalizer test case.
 */
class SprintfTest extends TestCase {

    /**
     * Tests Internationalizer::get()
     */
    public function testGet()
    {
        $result = 'test one two three test';
        $nResult = 'test 1.23 test';
        
    	/* @var $stub \Princeton\App\Strings\Internationalizer */
    	$stub = $this->getMockForAbstractClass('Princeton\App\Strings\Internationalizer\Sprintf', array($this->createMock(Cache::class), 'en_US'));
        
    	$this->assertEquals($result, $stub->get('sprintf.none'));
        $this->assertEquals($result, $stub->get('sprintf.one', 'one'));
        $this->assertEquals($result, $stub->get('sprintf.two', 'one', 'two'));
        $this->assertEquals($result, $stub->get('sprintf.two', ['one', 'two']));
        $this->assertEquals($result, $stub->get('sprintf.reverse', ['three', 'two', 'one']));
        $this->assertEquals($nResult, $stub->get('sprintf.numeric', 1.234567));
    }
}
