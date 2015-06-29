<?php

namespace Princeton\App\Formatter;

use SimpleXMLElement;

class XHTMLFormatter extends Formatter
{
	private static $prefix = 'timeline-';
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
		
		$node = $this->addClassedChild($node, 'ul', 'results');
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
		foreach ($data as $key => $value) {
			if (is_array($value) || $value instanceof \Iterator) {
				$element = $this->addClassedChild($xml, 'li', $key)->addChild('ul');
				$this->build($element, $value);
			} elseif (is_object($value)) {
				if (is_callable(array($value, 'asArray'))) {
					$element = $this->addClassedChild($xml, 'li', $key);
					$this->build($element, $value->{'asArray'}());
				}
			} else {
				$this->addClassedChild($xml, 'span', $key, $value);
			}
		}
	}

	private function addClassedChild(SimpleXMLElement $parent, $name, $class, $value = null)
	{
		$item = $parent->addChild($name, htmlspecialchars($value));
		$item->addAttribute('class', self::$prefix . $class);
		return $item;
	}
}
