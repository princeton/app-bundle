<?php

namespace Princeton\Cache;

use Princeton\Traits\Cache;

/**
 * Reads a file from disk, optionally applies post-processing,
 * saves the processed data and a timestamp into the memory cache
 * for future requests, and returns the processed data.
 *
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2014 The Trustees of Princeton University.
 */
class CachedFile
{
	use Cache;

	protected $uid;
	protected $callable;
	protected $stat;
	
	/**
	 * Create a CachedFile manager.
	 *
	 * @param string $uid - If $callable is not null, then $uid should be non-empty
	 * 	and should uniquely identify this callable.
	 * @param string $callable - A callable of the form (mixed) callable(string).  Should return something that is serializable.
	 * @param boolean $stat - If cached data exists for a given file, fetch will check
	 *  whether the file's mtime has changed since the cached data was saved, unless stat is set to false.
	 *  (like the apc.stat ini setting.)
	 */
	public function __construct($uid = '', $callable = null, $stat = true)
	{
		$this->uid = $uid;
		$this->callable = $callable;
		$this->stat = $stat;
	}
	
	/**
	 * Either returns the stored data for this file from the cache
	 * or reads the file, processes the data by running it through $this->callable,
	 * saves the result to the cache and returns it.
	 *
	 * It also saves a timestamp with the data, which it compares against the file's timestamp
	 * in order to determine whether to use the cached data.
	 *
	 * Subsequent calls to fetch() do NOT re-run $this->callable.
	 *
	 * @param string $filename - The name of the file to read.
	 * @return mixed - The processed data.
	 */
	public function fetch($filename)
	{
		// Canonicalize.
		$filename = realpath($filename);
		if ($filename) {
			/* @var $cache \Doctrine\Common\Cache\Cache */
			$cache = $this->getCache();
			$cached = $cache->fetch($this->uid . $filename);
			if (isset($cached)) {
				if ($this->stat) {
					$cacheTime = $cached[0];
					$fileTime = filemtime($filename);
					if ($fileTime > 0 && $cacheTime > $fileTime) {
						return $cached[1];
					}
				} else {
					return $cached[1];
				}
			}
			$now = time();
			$data = file_get_contents($filename);
			
			if ($this->callable) {
				$callable = $this->callable;
				$data = $callable($data);
			}
			$cache->save($this->uid . $filename, array($now, $data), 0);
			return $data;
		}
	}
}

