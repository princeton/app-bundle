<?php

namespace Princeton\App\Adapter;

use Princeton\App\Exceptions\ApplicationException;

abstract class LdapAdapter extends BaseAdapter
{
	protected $modifiedSince;
	protected $modifiedUntil;
	protected $service;

	public function __construct($params, $modifiedSince = null, $modifiedUntil = null)
	{
		parent::__construct($params);
		$this->modifiedSince = $modifiedSince;
		$this->modifiedUntil = $modifiedUntil;
		$this->registerParams(array(
			'server' => null,
			'base' => null,
			'user' => null,
			'password' => null,
			'filter' => null,
			'fields' => null,
		));
	}
	
	protected function bind()
	{
		$server = $this->param('server');
		if (!isset($server)) {
			throw new ApplicationException('No server configured for LdapAdapter');
		}

		$ds = @ldap_connect($server);
		$password = $this->param('password');
		$bindQuery = $this->param('user') . ',' . $this->param('base');
		
		if (!isset($password) && $ds && ldap_bind($ds)) {
			$this->service = $ds;
		} else if ($ds && ldap_bind($ds, $bindQuery, $password)) {
			$this->service = $ds;
		} else {
			throw new ApplicationException('LDAP bind failed.');
		}
	}

	public function retrieve()
	{
		$entries = array('count' => 0);
		$filter = $this->param('filter');

		$until = $this->ldapDate($this->modifiedUntil);
		$since = empty($this->modifiedSince) ? 'x' : $this->ldapDate($this->modifiedSince);
		$whenFilter = "modifytimestamp=$until";
		foreach (range(0, strlen($until)) as $n) {
			if ($until[$n] !== $since[$n]) {
				$whenFilter = 'modifytimestamp=' . substr($until, 0, $n) . '*';
				break;
			}
		}
		// TODO Could still restrict allowed values of subsequent digit...
		$filter = "(&($whenFilter)($filter))";

		if (!$this->service) {
			$this->bind();
		}
		
		$sr = @ldap_search($this->service, $this->param('base'), $filter, explode(',', $this->param('fields')));
		if ($sr && ldap_count_entries($this->service, $sr) > 0) {
			$entries = ldap_get_entries($this->service, $sr);
		}
		return $entries;
	}
	
	public function parse($data)
	{
		return $this->flatten($data);
	}

	public function flatten($entries)
	{
		$results = array();
		if ($entries['count'] > 0) {
			foreach (range(0, $entries['count']-1) as $index) {
				$entry = $entries[$index];
				$result = array();
				for ($n = 0; $n < $entry['count']; $n += 1) {
					$attribute = $entry[$n];
					if ($entry[$attribute]['count'] > 1) {
						for ( $j = 0; $j < $entry[$attribute]['count']; $j++ ) {
							$result[$attribute][] = $entry[$attribute][$j];
						}
					} else {
						$result[$attribute] = $entry[$attribute][0];
					}
				}
				$results[] = $result;
			}
		}
		return $results;
	}
	
	/*
	 * For use in LDAP queries, modification timestamps must be in UTC, in the format
	 * "YYYYmmddHHiiss\Z". That is, the end of 2014 (at 0 longitude) would be written "20141231235959Z".
	 */
	/* @var $date \DateTime */
	protected function ldapDate($date = null)
	{
		if (empty($date)) {
			$date = new \DateTime();
		} else {
			$date = \DateTime::createFromFormat('U.u', sprintf('%.6F', $date/1000));
		}
		$date->setTimezone(new \DateTimeZone('UTC'));
		return $date->format('YmdHis\Z');
	}
}

