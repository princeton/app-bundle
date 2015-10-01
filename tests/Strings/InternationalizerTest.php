<?php

namespace Test\Strings;

use Test\TestCase;

/**
 * Internationalizer test case.
 */
class InternationalizerTest extends TestCase {

    /**
     * Tests Internationalizer::get()
     */
    public function testGet()
    {
    	/* @var $stub \Princeton\App\Strings\Internationalizer */
    	$stub = $this->getMockForAbstractClass('Princeton\App\Strings\Internationalizer', array('en_US'));
        
    	$this->assertEquals('http://example.com', $stub->get('urlbase'));
    }
}
