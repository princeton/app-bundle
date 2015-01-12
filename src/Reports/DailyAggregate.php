<?php

namespace Princeton\App\Reports;

class DailyAggregate extends \Princeton\App\DataModel\DocumentObject
{
	protected $id;
	protected $type;
	protected $value;
	protected $day;
	protected $total;
	protected $hourly;
	
	public function __construct($minsStep = 1)
	{
		parent::__construct();
		$this->total = 0;
		// Mongo doesn't like having '0' as hash key - '00' seems fine though.
		$hourData = array_fill_keys(array_map(function ($n) { return ($n<10) ? ('0' . $n) : $n; }, range(0, 59, $minsStep)), 0);
		$hourData['total'] = 0;
		$this->hourly = array_fill(0, 24, $hourData);
	}
	
	public function id()
	{
		return $this->id;
	}
	
	public function setDay(\DateTime $day)
	{
		$day->setTime(0, 0);
		return $this->day = $day;
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
