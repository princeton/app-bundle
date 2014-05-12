<?php

namespace Princeton\Reports;

use Princeton\DataModel\DocumentObject;

class MonthlyAggregate extends DocumentObject
{
	protected $id;
	protected $type;
	protected $value;
	protected $month;
	protected $total;
	protected $daily;
	
	public function __construct($hoursStep = 1)
	{
		parent::__construct();
		$this->total = 0;
		$dayData = array_fill_keys(range(0, 24, $hoursStep), 0);
		$dayData['total'] = 0;
		$this->daily = array_fill(0, 31, $dayData);
	}
	
	public function id()
	{
		return $this->id;
	}
	
	public function setMonth(\DateTime $month)
	{
		$month->setTime(0, 0);
		$month->setDate($month->format('Y'), $month->format('n'), 1);
		return $this->month = $month;
	}
	
	public function __get($name)
	{
		return $this->{$name};
	}
	
	public function __set($name, $value)
	{
		return $this->{$name} = $value;
	}
}
