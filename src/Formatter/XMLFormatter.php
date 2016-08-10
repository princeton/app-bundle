<?php

namespace Princeton\App\Formatter;

use SimpleXMLElement;

class XMLFormatter extends Formatter
{
    private static $rootXml = '<?xml version="1.0" encoding="UTF-8" ?><result></result>';

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
        $root->addChild('message', htmlspecialchars($msg));
        
        if ($ex) {
            $this->build($root->addChild('exception'), $ex);
        }
        
        return $root->asXML();
    }

    private function build(SimpleXMLElement $xml, $data)
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
                $element = $xml->addChild($xkey, htmlspecialchars($value));
            }
            
            if (is_numeric($key) && $element) {
                $element->addAttribute('index', $key);
            }
        }
    }
}
