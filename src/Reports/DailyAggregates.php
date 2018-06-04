<?php

namespace Princeton\App\Reports;

class DailyAggregates extends \Doctrine\ODM\MongoDB\DocumentRepository
{
	protected $step = 1;
	protected $expectedHits = 10000;

	public function log($data)
	{
		$today = new \DateTime();
		$hour = $today->format('G');
		$min = $today->format('i');
		if ($this->step > 1) {
			$min = floor($min/$this->step) * $this->step;
		}
		if ($min < 10) {
			$min = '0' . $min;
		}
		$today->setTime(0, 0);
		$dayIncr = new \DateInterval('P1D');

		foreach ($data as $type => $value) {
			$this->errlog('incrementing for '.$this->makeId($type, $value, $today).'  '."hourly.$hour.total".' '."hourly.$hour.$min");
			$query = $this->createQueryBuilder()
				->field('id')->equals($this->makeId($type, $value, $today))
				->update()
				->field("total")->inc(1)
				->field("hourly.$hour.total")->inc(1)
				->field("hourly.$hour.$min")->inc(1)
				->getQuery();
			$status = $query->execute();

			if (!$status['updatedExisting']) {
				$this->errlog('making '.$this->getClassName().' with '.$value.' '.$today->format(\DateTime::W3C));
				$this->makeItem($type, $value, $today);
				$query->execute();
			}

			if (mt_rand(0, $this->expectedHits) === 1) {
				$this->errlog('making '.$this->getClassName().' with '.$value.' '.$today->format(\DateTime::W3C));
				$this->makeItem($type, $value, $today->add($dayIncr));
			}
		}
	}

	private function makeId($type, $value, \DateTime $date)
	{
		return "daily-$type-$value-" . $date->format(\DateTime::W3C);
	}

	private function errlog($message)
	{
		$now = new \DateTime();
		error_log($now->format(\DateTime::W3C).' '.$message);
	}

	private function makeItem($type, $value, $date)
	{
		$class = $this->getClassName();
		/* @var $obj DailyAggregate */
		$obj = new $class($this->step);
		$obj->type = $type;
		$obj->value = $value;
		$obj->setDay($date);
		$obj->id = $this->makeId($type, $value, $obj->day);
		$this->dm->persist($obj);
		$this->dm->flush($obj);
	}
}
