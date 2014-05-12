<?php

namespace Princeton\Adapter;

interface Adapter
{
	public function retrieve();
	public function parse($data);
	public function provide($objects);
}
