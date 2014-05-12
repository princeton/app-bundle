<?php

namespace Princeton\Adapter;

abstract class BaseAdapter implements Adapter
{
	private $params = array();
	private $registry = array();

	public function __construct($params)
	{
		if ($params) {
			foreach ($params as $key => $value) {
				// TODO could end up with params set that we shouldn't have set!
				$this->params[$key] = $value;
			}
		}
	}

	public function param($key, $default = null)
	{
		return isset($this->params[$key]) ? $this->params[$key] : $default;
	}

	public function setParam($key, $value)
	{
		if (array_key_exists($key, $this->registry)) {
			$this->params[$key] = $value;
		}
	}

	public function hasParam($key)
	{
		return array_key_exists($key, $this->registry);
	}

	public function registerParam($key, $default = null)
	{
		if (!array_key_exists($key, $this->params)) {
			$this->params[$key] = $default;
		}
		$this->registry[$key] = $default;
	}

	protected function registerParams($values)
	{
		foreach ($values as $key => $default) {
			$this->registerParam($key, $default);
		}
	}

	public function paramNames()
	{
		return array_keys($this->params);
	}

	protected function hideParam($key, $default)
	{
		$this->setParam($key, $default);
		unset($this->registry[$key]);
	}

	public function perform(){
		return $this->provide($this->parse($this->retrieve()));
	}
	
	public static function getInstance($serviceIndex)
	{
		// TODO implement getInstance()
		return null;
	}
}
