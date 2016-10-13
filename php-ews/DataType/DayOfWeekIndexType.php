<?php

namespace PhpEws\DataType;

use PhpEws\DataType;

/**
 * Definition of the DayOfWeekIndexType type.
 */
class DayOfWeekIndexType extends DataType
{
    /**
     * Represents the first week of the month.
     *
     * @var string
     */
    const FIRST = 'First';

    /**
     * Represents the second week of the month.
     *
     * @var string
     */
    const SECOND = 'Second';

    /**
     * Represents the third week of the month.
     *
     * @var string
     */
    const THIRD = 'Third';

    /**
     * Represents the fourth week of the month.
     *
     * @var string
     */
    const FOURTH = 'Fourth';

    /**
     * Represents the last week of the month.
     *
     * @var string
     */
    const LAST = 'Last';

    /**
     * Element value.
     *
     * @var string
     */
    public $_;

    /**
     * Returns the value of this object as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_;
    }
}
