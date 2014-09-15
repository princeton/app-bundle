<?php

namespace Princeton\App\DataModel;

use DateTime;

class DocumentObject implements \JsonSerializable
{
	protected $id;
	protected $active;
	protected $created;
	protected $lastModified;

	public function __construct()
	{
		$this->created = $this->lastModified = new DateTime();
		$this->active = true;
	}

	public function id()
	{
		return $this->id;
	}

	public function isActive()
	{
		return ($this->active === true);
	}

	public function activate()
	{
		$this->active = true;
	}

	public function deactivate()
	{
		$this->active = false;
	}
	
	public function asArray()
	{
		return array(
			'id' => $this->id,
			'active' => $this->active,
			'created' => $this->created,
			'lastModified' => $this->lastModified,
		);
	}
	
	public function jsonSerialize()
	{
		return $this->asArray();
	}
}
