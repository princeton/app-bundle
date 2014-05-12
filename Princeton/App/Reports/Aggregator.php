<?php

namespace Princeton\App\Reports;

/**
 * This is just a thought about how to generalize this better.
 * Still off though.
 *
 * TODO What is needed is a way for it to match a classname to each $type,
 * so the type can determine the appropriate reporting increment.
 */
class Aggregator extends \Doctrine\ODM\MongoDB\DocumentRepository
{
	public function log($data)
	{
		$class = $this->getClassName();
		$step = $class::getStep();
		if ($class::isMonthly()) {
			$div1 = 'j';
			$div2 = 'G';
			$expectedHits = 300000;
			$dayIncr = new \DateInterval('P1M');
			$divName = 'daily';
		} else {
			$div1 = 'G';
			$div2 = 'i';
			$expectedHits = 10000;
			$dayIncr = new \DateInterval('P1D');
			$divName = 'hourly';
		}
		$today = new \DateTime();
		$hour = $today->format($div1);
		$min = 0 + $today->format($div2); // drop leading '0'!
		if ($step > 1) {
			$min = floor($min/$step) * $step;
		}
		$today->setTime(0, 0);
		
		foreach ($data as $type => $value) {
			$query = $this->createQueryBuilder()
				->field('id')->equals($this->makeId($type, $value, $today))
				->update()
				->field("total")->inc(1)
				->field("$divName.$hour.total")->inc(1)
				->field("$divName.$hour.$min")->inc(1)
				->getQuery();
			$status = $query->execute();
			
			if (!$status['updatedExisting']) {
				$this->makeItem($type, $value, $today, $class);
				$query->execute();
			}
	
			if (mt_rand(0, $expectedHits) === 1) {
				$this->makeItem($type, $value, $today->add($dayIncr), $class);
			}
		}
	}

	private function makeId($type, $value, \DateTime $date)
	{
		return "$type-$value-" . $date->format(\DateTime::W3C);
	}
	
	private function makeItem($type, $value, $date, $class)
	{
		/* @var $obj DailyAggregate */
		$obj = new $class();
		$obj->id = $this->makeId($type, $value, $date);
		$obj->type = $type;
		$obj->value = $value;
		$obj->setDay($date);
		$this->dm->persist($obj);
		$this->dm->flush($obj);
	}
}
