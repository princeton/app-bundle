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
		$this->build($root->addChild('data'), $data);
		return $root->asXML();
	}

	public function error($msg, $ex = null)
	{
		$root = new SimpleXMLElement(self::$rootXml);
		$root->addChild('timestamp', time());
		$root->addChild('status', 'error');
		$root->addChild('message', htmlspecialchars($msg));
		$root->addChild('exception', htmlspecialchars($ex));
		return $root->asXML();
	}

	private function build(SimpleXMLElement $xml, $data)
	{
		foreach ($data as $key => $value) {
			if (is_array($value) || $value instanceof \Iterator) {
				$element = $xml->addChild($key);
				$this->build($element, $value);
			} elseif (is_object($value)) {
				if (is_callable(array($value, 'asArray'))) {
					$element = $xml->addChild($key);
					$this->build($element, $value->{'asArray'}());
				}
			} else {
				$xml->addChild($key, htmlspecialchars($value));
			}
		}
	}
}