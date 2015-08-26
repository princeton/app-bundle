<?php

namespace Princeton\App\Formatter;

use SimpleXMLElement;

class XHTMLFormatter extends Formatter
{
    private static $prefix = 'timeline-';
    private static $prefix2 = 'timeline-result-';
    private static $rootXml = '<?xml version="1.0" encoding="UTF-8" ?><html></html>';

    public function format($data)
    {
        $root = new SimpleXMLElement(self::$rootXml);
        $root->addAttribute('xsi:schemaLocation', 'http://www.w3.org/TR/2009/PER-xhtml11-20090507/xhtml11_schema.html');
        $head = $root->addChild('head');
        $head->addChild('title', 'Timeline Results');
        
        $node = $root->addChild('body');
        $this->addClassedChild($node, 'div', 'timestamp', date('Y-m-d H:i:s'));
        $this->addClassedChild($node, 'div', 'status', 'ok');
        
        $node = $this->addClassedChild($node, 'div', 'results');
        $this->build($node, $data);
        
        $this->modifyHook($root);
        
        return $root->asXML();
    }

    public function error($msg, $ex = null)
    {
        $root = new SimpleXMLElement(self::$rootXml);
        $root->addAttribute('xsi:schemaLocation', 'http://www.w3.org/TR/2009/PER-xhtml11-20090507/xhtml11_schema.html');
        $root->addAttribute('class', self::$prefix . 'results');
        $this->addClassedChild($root, 'div', 'timestamp', date('Y-m-d H:i:s'));
        $this->addClassedChild($root, 'div', 'status', 'error');
        $this->addClassedChild($root, 'div', 'message', $msg);
        $this->addClassedChild($root, 'div', 'exception', $ex);
        return $root->asXML();
    }
    
    protected function modifyHook($root)
    {
        // No-op. To be overridden as needed by subclasses.
    }
    
    private function build(SimpleXMLElement $xml, $data)
    {
        if (is_object($data)) {
            if (is_callable($data, 'asArray')) {
                $data = $data->{'asArray'}();
            } else {
                $data = (array)$data;
            }
        }
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $index = $key;
                $key = 'item';
                $tag = 'li';
            } else {
                $index = null;
                $tag = 'div';
            }
            
            if (is_array($value) && isset($value[0])) {
                $element = $this->addResultChild($xml, $tag, $key);
                $this->build($element->addChild('ul'), $value);
            } elseif (is_array($value)) {
                $element = $this->addResultChild($xml, $tag, $key);
                $this->build($element, $value);
            } elseif (is_object($value)) {
                if (is_callable(array($value, 'asArray'))) {
                    $element = $this->addResultChild($xml, $tag, $key);
                    $this->build($element, $value->{'asArray'}());
                } else {
                    $element = null;
                }
            } else {
                if ($value === true) {
                   $value = 'true';
                } elseif ($value === false) {
                   $value = 'false';
                }
                $element = $this->addResultChild($xml, $tag, $key, htmlspecialchars($value));
            }
                
            if (isset($index) && $element) {
                $element->addAttribute('index', $index);
            }
        }
    }

    private function addClassedChild(SimpleXMLElement $parent, $name, $class, $value = null)
    {
        $item = $parent->addChild($name, htmlspecialchars($value));
        $item->addAttribute('class', self::$prefix . $class);
        return $item;
    }

    private function addResultChild(SimpleXMLElement $parent, $name, $class, $value = null)
    {
        $item = $parent->addChild($name, htmlspecialchars($value));
        $item->addAttribute('class', self::$prefix2 . $class);
        return $item;
    }
}
