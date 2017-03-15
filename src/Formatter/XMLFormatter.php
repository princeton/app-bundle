<?php

namespace Princeton\App\Formatter;

use SimpleXMLElement;

class XMLFormatter extends Formatter
{
    protected static $rootXml = '<?xml version="1.0" encoding="UTF-8" ?><result></result>';

    /**
     * Filters control characters but allows only properly-formed surrogate sequences.
     */
    protected static $invalidXMLChars = '/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]+/u';

    public function format($data)
    {
        $root = new SimpleXMLElement(self::$rootXml);
        $root->addChild('timestamp', time());
        $root->addChild('status', 'ok');

        $node = $root->addChild('data');

        if ($data) {
            $this->build($node, $data);
        }

        return $root->asXML();
    }

    public function error($msg, $ex = null)
    {
        $root = new SimpleXMLElement(self::$rootXml);
        $root->addChild('timestamp', time());
        $root->addChild('status', 'error');
        $root->addChild('message', $this->cleanText($msg));

        if ($ex) {
            /* @var $ex \Exception */
            $exarr = [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'trace' => $ex->getTraceAsString(),
            ];
            $this->build($root->addChild('exception'), $exarr);
        }

        return $root->asXML();
    }

    protected function build(SimpleXMLElement $xml, $data)
    {
        if (is_object($data)) {
            if (is_callable($data, 'asArray')) {
                $data = $data->asArray();
            } else {
                $data = (array)$data;
            }
        }
        foreach ($data as $key => $value) {
            $xkey = is_numeric($key) ? "item" : $key;
            if (is_array($value) || $value instanceof \Iterator) {
                $element = $xml->addChild($xkey);
                $this->build($element, $value);
            } elseif (is_object($value)) {
                $element = $xml->addChild($xkey);
                $method = 'asArray';

                if (is_callable(array($value, $method))) {
                    $this->build($element, $value->{$method}());
                } else {
                    $this->build($element, (array)$value);
                }
            } else {
                if ($value === true) {
                   $value = 'true';
                } elseif ($value === false) {
                   $value = 'false';
                }
                $element = $xml->addChild($xkey, $this->cleanText($value));
            }

            if (is_numeric($key) && $element) {
                $element->addAttribute('index', $key);
            }
        }
    }

    /**
     * Removes any unusual unicode characters that can't be encoded into XML, and makes entities where needed.
     */
    protected function cleanText($text)
    {
        return preg_replace(self::$invalidXMLChars, '', htmlspecialchars($text, ENT_NOQUOTES));
    }
}
