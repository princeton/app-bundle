<?php

namespace Princeton\App\Adapter;

abstract class JsonAdapter extends HttpAdapter
{
	public function parse($data)
	{
		return json_decode($data, true);
	}
}
