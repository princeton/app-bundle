<?php

namespace Princeton\Formatter;

class JSONFormatter extends Formatter
{
	public function format($data)
	{
		return json_encode($data);
	}

	public function error($msg, $ex = null)
	{
		return json_encode(array('message' => $msg, 'exception' => $ex));
	}
}
