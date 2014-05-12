<?php

namespace Princeton\Formatter;

abstract class Formatter
{
		abstract public function format($data);

		abstract public function error($msg, $ex);

		public static function getFormatter($type)
		{
			$type = preg_replace('/[^a-zA-Z0-9_]/', '', $type);
			if (strlen($type) > 0) {
				$type = '\Princeton\Formatter\\' . $type . 'Formatter';
				return new $type();
			}
			return false;
		}
}
