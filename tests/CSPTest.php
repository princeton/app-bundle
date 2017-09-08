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
        $defaults = "object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'";
    	/* @var $stub \Princeton\App\CSP */
    	$stub = $this->getMockForAbstractClass(CSP::class);
    	$this->assertEquals("default-src 'none'; $defaults", (string)$stub);

    	$stub = $this->getMockForAbstractClass('Princeton\App\CSP', [[
            'default-src' => CSP::SELF,
            'script-src' => [CSP::SELF, 'http://example.com'],
        ]]);
    	$this->assertEquals("default-src 'self'; $defaults; script-src 'self' http://example.com", (string)$stub);
    }
}
