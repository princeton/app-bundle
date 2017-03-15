<?php

namespace Test\Formatter;

use Exception;
use Test\TestCase;
use Princeton\App\Formatter\XHTMLFormatter;

/**
 * XHTMLFormatter test case.
 */
class XHTMLFormatterTest extends TestCase
{
    /**
     * @covers Princeton\App\Formatter\XHTMLFormatter
     */
    public function testFormat()
    {
        $subject = new XHTMLFormatter();
        $expected = '#^<\?xml version="1.0" encoding="UTF-8"\?>'
            . "\n" . '<html schemaLocation="[^"]*"><head><title>Results</title></head>'
            . '<body name="results" class="results"><div name="header" class="header">'
            . '<div name="timestamp" class="timestamp">[0-9: -]+</div>'
            . '<div name="status" class="status">ok</div></div>'
            . '<div name="data" class="data"><div name="a" class="a">b</div>'
            . '<div name="c" class="c"><div name="d" class="d">123</div></div></div>'
            . '</body></html>$#';
        $actual = $subject->format(['a' => 'b', 'c' => ['d' => 123]]);
        $this->assertRegExp($expected, $actual);
    }

    /**
     * @covers Princeton\App\Formatter\XHTMLFormatter
     */
    public function testError()
    {
        $subject = new XHTMLFormatter();
        $expected = '#<div name="status" class="status">error</div><div name="message" class="message">test message</div>'
            . '<div name="exception" class="exception">.*</div></div></div>'
            . '<div name="data" class="data"></div></body></html>#s';
        $actual = $subject->error('test message', new Exception('test', 42));
        $this->assertRegExp($expected, $actual);
    }
}
