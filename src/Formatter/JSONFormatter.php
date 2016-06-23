<?php

namespace Princeton\App\Formatter;

class JSONFormatter extends Formatter
{
    /**
     * Bit mask of JSON_* options to be passed to json_encode.
     * @var int
     */
    public $options = 0;
    
	public function format($data)
	{
		return json_encode($data, $this->options);
	}

	public function error($msg, $ex = null)
	{
		return json_encode(array('message' => $msg, 'exception' => $ex), $this->options);
	}
}
