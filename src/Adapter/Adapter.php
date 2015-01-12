<?php

namespace Princeton\App\Adapter;

interface Adapter
{
	public function retrieve();
	public function parse($data);
	public function provide($objects);
}
