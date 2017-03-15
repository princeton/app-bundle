<?php

namespace Princeton\App\Formatter;

use SimpleXMLElement;

class XHTMLFormatter extends XMLFormatter
{
    protected static $rootXml = '<?xml version="1.0" encoding="UTF-8" ?><html></html>';

    protected $prefix = '';

    public function format($data)
    {
        $root = new SimpleXMLElement(self::$rootXml);
        $root->addAttribute('xsi:schemaLocation', 'http://www.w3.org/TR/2009/PER-xhtml11-20090507/xhtml11_schema.html');
        $head = $root->addChild('head');
        $head->addChild('title', 'Results');

        $body = $this->addClassedChild($root, 'body', 'results');

        $node = $this->addClassedChild($body, 'div', 'header');
        $this->addClassedChild($node, 'div', 'timestamp', date('Y-m-d H:i:s'));
        $this->addClassedChild($node, 'div', 'status', 'ok');

        $node = $this->addClassedChild($body, 'div', 'data');

        if ($data) {
            $this->build($node, $data);
        }

        $this->modifyHook($root);

        return $root->asXML();
    }

    public function error($msg, $ex = null)
    {
        $root = new SimpleXMLElement(self::$rootXml);
        $root->addAttribute('xsi:schemaLocation', 'http://www.w3.org/TR/2009/PER-xhtml11-20090507/xhtml11_schema.html');

        $head = $root->addChild('head');
        $head->addChild('title', 'Error');

        $body = $this->addClassedChild($root, 'body', 'error');

        $node = $this->addClassedChild($body, 'div', 'header');
        $this->addClassedChild($node, 'div', 'timestamp', date('Y-m-d H:i:s'));
        $this->addClassedChild($node, 'div', 'status', 'error');
        $this->addClassedChild($node, 'div', 'message', $msg);

        if ($ex) {
            /* @var $ex \Exception */
            $exarr = [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'trace' => $ex->getTraceAsString(),
            ];
            $this->build($this->addClassedChild($node, 'div', 'exception'), $exarr);
        }

        // Empty data tag.
        $node = $this->addClassedChild($body, 'div', 'data');

        return $root->asXML();
    }

    protected function modifyHook($root)
    {
        // No-op. To be overridden as needed by subclasses.
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
            if (is_numeric($key)) {
                $index = $key;
                $key = 'item';
                $tag = 'li';
            } else {
                $index = null;
                $tag = 'div';
            }

            if (is_array($value) && isset($value[0])) {
                $element = $this->addClassedChild($xml, $tag, $key);
                $this->build($element->addChild('ul'), $value);
            } elseif (is_array($value)) {
                $element = $this->addClassedChild($xml, $tag, $key);
                $this->build($element, $value);
            } elseif (is_object($value)) {
                $element = $this->addClassedChild($xml, $tag, $key);

                if (is_callable(array($value, 'asArray'))) {
                    $this->build($element, $value->asArray());
                } else {
                    $this->build($element, (array)$value);
                }
            } else {
                if ($value === true) {
                   $value = 'true';
                } elseif ($value === false) {
                   $value = 'false';
                }

                $element = $this->addClassedChild($xml, $tag, $key, $this->cleanText($value));
            }

            if (isset($index) && $element) {
                $element->addAttribute('index', $index);
            }
        }
    }

    protected function addClassedChild(SimpleXMLElement $parent, $tag, $name, $value = null)
    {
        $item = $parent->addChild($tag, $this->cleanText($value));
        $item->addAttribute('name', $name);
        $item->addAttribute('class', $this->prefix . $name);

        return $item;
    }
}
