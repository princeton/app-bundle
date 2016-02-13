<?php

namespace Test;

use Test\TestCase;
use Princeton\App\CSP;

/**
 * CSP test case.
 */
class CSPTest extends TestCase {

    /**
     * Tests CSP::__toString()
     */
    public function testToString()
    {
    	/* @var $stub \Princeton\App\CSP */
    	$stub = $this->getMockForAbstractClass('Princeton\App\CSP');
    	$this->assertEquals("default-src 'none'", (string)$stub);

    	$stub = $this->getMockForAbstractClass('Princeton\App\CSP', [[
            'default-src' => CSP::SELF,
            'script-src' => [CSP::SELF, 'http://example.com'],
        ]]);
    	$this->assertEquals("default-src 'self'; script-src 'self' http://example.com", (string)$stub);
    }
}
