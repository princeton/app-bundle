<?php

namespace Princeton\Adapter;

class InternalAdapter extends BaseAdapter
{
	public function provide($objects)
	{
		return true;
	}

	public function parse($data)
	{
		return array();
	}

	public function retrieve()
	{
		return null;
	}
}
