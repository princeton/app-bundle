<?php

namespace Princeton\App\Adapter;

use Princeton\App\Exceptions\ApplicationException;

/*
 * Should retrieve emails from an Imap mailbox and post them.
 */
abstract class ImapAdapter extends BaseAdapter
{
	public function __construct($params)
	{
		parent::__construct($params);
		$this->registerParams(array(
			'server' => null,
			'user' => null,
			'password' => null,
			'eventType' => 'announcement',
		));
	}

	public function retrieve()
	{
		$server = $this->param('server');
		if (!isset($server)) {
			throw new ApplicationException('No server configured for ImapAdapter');
		}
		// TODO implement ImapAdapter::retrieve()
	}

	public function parse()
	{
		// TODO implement ImapAdapter::parse()
	}
}
