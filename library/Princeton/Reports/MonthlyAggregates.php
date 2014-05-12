<?php

namespace Princeton\Reports;

class MonthlyAggregates extends \Doctrine\ODM\MongoDB\DocumentRepository
{
	protected $step = 1;
	protected $expectedHits = 300000;

	public function log($data)
	{
		$today = new \DateTime();
		$day = $today->format('j');
		$hour = $today->format('G');
		if ($this->step > 1) {
			$hour = floor($hour/$this->step) * $this->step;
		}
		$today->setTime(0, 0);
		$today->setDate($today->format('Y'), $today->format('n'), 1);
		$monthIncr = new \DateInterval('P1M');
		
		foreach ($data as $type => $value) {
			$query = $this->createQueryBuilder()
				->field('id')->equals($this->makeId($type, $value, $today))
				->update()
				->field("total")->inc(1)
				->field("daily.$day.total")->inc(1)
				->field("daily.$day.$hour")->inc(1)
				->getQuery();
			$status = $query->execute();
			
			if (!$status['updatedExisting']) {
				$this->makeItem($type, $value, $today);
				$query->execute();
			}
	
			if (mt_rand(0, $this->expectedHits) === 1) {
				$this->makeItem($type, $value, $today->add($monthIncr));
			}
		}
	}

	private function makeId($type, $value, \DateTime $date)
	{
		return "monthly-$type-$value-" . $date->format(\DateTime::W3C);
	}
	
	private function makeItem($type, $value, $date)
	{
		$obj = new MonthlyAggregate($this->step);
		$obj->id = $this->makeId($type, $value, $date);
		$obj->type = $type;
		$obj->value = $value;
		$obj->setMonth($date);
		$this->dm->persist($obj);
		$this->dm->flush($obj);
	}
}
