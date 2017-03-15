<?php

namespace Test\Formatter;

use Exception;
use Test\TestCase;
use Princeton\App\Formatter\XMLFormatter;

/**
 * XMLFormatter test case.
 */
class XMLFormatterTest extends TestCase
{
    /**
     * @covers Princeton\App\Formatter\XMLFormatter
     */
    public function testFormat()
    {
        $subject = new XMLFormatter();
        $expected = '#^<\?xml version="1.0" encoding="UTF-8"\?>'
            . "\n" . '<result><timestamp>[0-9]{10}</timestamp><status>ok</status><data><a>b</a><c><d>123</d></c></data></result>$#';
        $actual = $subject->format(['a' => 'b', 'c' => ['d' => 123]]);
        $this->assertRegExp($expected, $actual);
    }

    /**
     * @covers Princeton\App\Formatter\XMLFormatter
     */
    public function testError()
    {
        $subject = new XMLFormatter();
        $expected = '#^<\?xml version="1.0" encoding="UTF-8"\?>'
            . "\n" . '<result><timestamp>[0-9]{10}</timestamp><status>error</status><message>test message</message>'
            . '<exception>.*</exception>'
            . '</result>#s';
        $actual = $subject->error('test message', new Exception('test', 42));
        $this->assertRegExp($expected, $actual);
    }
}
