<?php

namespace Princeton\App\DataModel;

use DateTime;
use DateTimeZone;
use JsonSerializable;

/**
 * A generic base class for MongoDB Document classes.
 *
 * @property string $id - The document's unique ID.
 * @property boolean $active - Whether the record is active or not.
 * @property int $created - Timestamp of when the record was created, in milliseconds.
 * @property int $lastModified - Timestamp of when the record was last modified, in milliseconds.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class DocumentObject implements JsonSerializable
{
    static private $tz;

    protected $id;

    protected $active;

    protected $created;

    protected $lastModified;

    public function __construct()
    {
        $this->created = $this->lastModified = self::currentTimeMillis();
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
        $this->updateTimestamp();
    }

    public function deactivate()
    {
        $this->active = false;
        $this->updateTimestamp();
    }

    public function updateTimestamp()
    {
        $this->lastModified = self::currentTimeMillis();
    }

    public function asArray()
    {
        return array(
            'id' => $this->id,
            'active' => $this->active,
            'created' => $this->created,
            'lastModified' => $this->lastModified
        );
    }

    public function jsonSerialize()
    {
        return $this->asArray();
    }

    public static function currentTimeMillis()
    {
        return (int) (microtime(true) * 1000);
    }

    public static function millisToDateTime($millis)
    {
        if (!self::$tz) {
            self::$tz = new DateTimeZone(date_default_timezone_get());
        }

        $ts = sprintf('%.6F', $millis/1000);
        return DateTime::createFromFormat('U.u', $ts)->setTimezone(self::$tz);
    }
}
