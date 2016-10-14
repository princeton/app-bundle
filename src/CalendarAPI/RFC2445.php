<?php
/**
 * This class implements some basic RFC2445 (iCal) utility methods.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2016 The Trustees of Princeton University
 */

namespace Princeton\App\CalendarAPI;

use DateTime;
use DateTimeZone;

abstract class RFC2445
{
    const SUNDAY        = "SU";
    const MONDAY        = "MO";
    const TUESDAY       = "TU";
    const WEDNESDAY     = "WE";
    const THURSDAY      = "TH";
    const FRIDAY        = "FR";
    const SATURDAY      = "SA";

    const FREQ_SECONDLY = "SECONDLY";
    const FREQ_MINUTELY = "MINUTELY";
    const FREQ_HOURLY   = "HOURLY";
    const FREQ_DAILY    = "DAILY";
    const FREQ_WEEKLY   = "WEEKLY";
    const FREQ_MONTHLY  = "MONTHLY";
    const FREQ_YEARLY   = "YEARLY";

    const OPAQUE        = 'OPAQUE';
    const TRANSPARENT   = 'TRANSPARENT';

    const LINESEP       = "\r\n";
    const DATE_FORMAT   = 'Ymd\THis\Z';

    /**
     * The mapping of strings to their escaped versions.
     */
    const ESCAPE_MAP    = [
        "\\" => "\\\\",
        "\t" => '    ',
        "\r" => '',
        "\n" => '\n',
        ';'  => '\;',
        ','  => '\,',
    ];

    /**
     * @var \DateTimeZone
     */
    protected $utc_tz;

    public function __construct()
    {
        $this->utc_tz = new \DateTimeZone('UTC');
    }

    /**
     * Formats the object for output.
     *
     * @return string
     */
    abstract public function __toString();

    /**
     * Formats the object for output.
     *
     * @return string
     */
    public function format()
    {
        return $this->__toString();
    }

    /**
     * Convert a \DateTime to RFC2445 date format.
     *
     * @param \DateTime $date
     * @return string
     */
    public function dateStr(DateTime $date)
    {
        $date->setTimezone($this->utc_tz);
        
        return $date->format(self::DATE_FORMAT);
    }

    protected function assemble($lines)
    {
        return join(self::LINESEP, $this->foldLines($lines)) . self::LINESEP;
    }

    /**
     * Fold each of the lines, as required by the spec.
     *
     * @param array $lines
     * @return string[]
     */
    protected function foldLines($lines)
    {
        $fold = function ($line) {
            return (strlen($line) > 74 ? substr(chunk_split($line, 74, self::LINESEP . ' '), 0, -3) : $line);
        };
        
        return array_map($fold, $lines);
    }

    /**
     * Make sure the string has been properly escaped.
     *
     * Using this method to escape text values produces output which validates against
     * http://severinghaus.org/projects/icv/
     *
     * ... AND Google Calendar likes it.
     *
     * Serge found a code snippet on the Web and Kevin modified it to actually match the iCal spec.
     *
     * The self::ESCAPE_MAP constant defines the escape mapping that we apply here.
     *
     * (Note that we are removing CR and replacing TAB with spaces,
     * and hoping that there are no other control characters in the stream).
     */
    protected function string($string)
    {
        return array_reduce(array_keys(self::ESCAPE_MAP), [$this, 'mapReplace'], $string);
    }
    
    private function mapReplace($string, $key)
    {
        return str_replace($key, self::ESCAPE_MAP[$key], $string);
    }
}
