<?php

namespace Princeton\App\Cache;

use Symfony\Component\Yaml\Parser;
use Princeton\App\Cache\Cache;

/**
 * A CachedFile that parses YAML files and caches the resulting YAML.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class CachedYaml extends CachedFile
{
	/**
	 * Create a CachedYaml manager.
	 *
	 * @param string $uid - Unique identifier associated with the given callable.
	 * @param string $callable - A callable which accepts the parsed YAML data, and should return something that is serializable.
	 * @param boolean $stat - Check the file's mtime against cache date if set to true.
	 * @see CachedFile.
	 */
	public function __construct(Cache $cache, $uid = '', $callable = null, $stat = true)
	{
		parent::__construct($cache, $uid . 'YAML-',
			function(&$data) use ($callable) {
				$yaml = new Parser();
				$output = $yaml->parse($data);
				if ($callable) {
					$output = $callable($output);
				}
				return $output;
			},
 			$stat);
	}
}

