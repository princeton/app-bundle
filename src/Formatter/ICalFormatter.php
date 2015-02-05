<?php

namespace Princeton\App\Formatter;


class ICalFormatter extends Formatter
{
    public function format($data)
    {
    	// Can't implement without knowing application data format.
    	return '';
    }

	public function error($msg, $ex = null)
	{
		return 'REQUEST-STATUS:3.00;' . $msg;
	}
}
