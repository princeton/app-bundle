<?php

namespace Princeton\App\Adapter;

abstract class XmlAdapter extends HttpAdapter
{
	public function parse($data)
	{
		return new \SimpleXMLElement($data);
	}
}
