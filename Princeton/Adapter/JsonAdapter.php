<?php

namespace Princeton\Adapter;

abstract class JsonAdapter extends HttpAdapter
{
	public function parse($data)
	{
		return json_decode($data, true);
	}
}
