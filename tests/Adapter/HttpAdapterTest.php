<?php

namespace Test\Adapter;

use Test\TestCase;

/**
 * HttpAdapter test case.
 */
class HttpAdapterTest extends TestCase {

    /**
     * Tests HttpAdapter::param()
     */
    public function testParam()
    {
    	/* @var $stub \Princeton\App\Adapter\HttpAdapter */
    	$stub = $this->getMockForAbstractClass('Princeton\App\Adapter\HttpAdapter', array(array('urlbase' => 'test')));
        
    	$this->assertEquals('test', $stub->param('urlbase', 'default'));
    }
}
