<?php

namespace Princeton\App\Adapter;

use Princeton\App\Exceptions\ApplicationException;

abstract class HttpAdapter extends BaseAdapter
{
	public function __construct($params)
	{
		parent::__construct($params);
		$this->registerParam('urlbase');
	}

	public function retrieve()
	{
		$urlbase = $this->param('urlbase');
		if (!isset($urlbase)) {
			throw new ApplicationException('No urlbase configured for HttpAdapter');
		}
		return file_get_contents($urlbase);
	}
}
