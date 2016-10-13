<?php

namespace PhpEws\DataType;

use PhpEws\DataType;

/**
 * Definition of the FirstWeeklyRecurrencePatternType type,
 * a patch to the supplied WeeklyRecurrencePatternType.
 */
class FirstWeeklyRecurrencePatternType extends DataType
{
    /**
     * DaysOfWeek property
     *
     * @var DaysOfWeekType
     */
    public $DaysOfWeek;

    /**
     * FirstDayOfWeek property
     *
     * @var DayOfWeekType
     */
    public $FirstDayOfWeek;
}
