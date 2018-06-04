<?php
/**
 * @author Kevin Perry, perry@princeton.edu
 * @copyright 2016 The Trustees of Princeton University.
 */

namespace Princeton\App\Cache;

use Princeton\App\Cache\Cache;

/**
 * A CachedFile that parses YAML files, does environment variable substitution
 * on strings of the form {$ENVAR}, and caches the resulting YAML.
 */
class CachedEnvYaml extends CachedYaml
{
    /**
     * Create a CachedEnvYaml manager.
     *
     * @param Cache $cache
     * @param string $uid - Unique identifier associated with the given callable.
     * @param string $callable - A callable which accepts the parsed YAML data, and should return something that is serializable.
     * @param boolean $stat - Check the file's mtime against cache date if set to true.
     * @see CachedFile.
     */
    public function __construct(Cache $cache, $uid = '', $callable = null, $stat = true)
    {
        $replacer = function ($matches)
        {
            return $_ENV[$matches[1]];
        };
        
        $matcher = function (&$value, $key) use ($replacer)
        {
            if (is_string($value) && strpos($value, '{$') !== false) {
                $value = preg_replace_callback('/{\$([a-zA-Z0-9_]+)}/', $replacer, $value);
            }
        };
        
        $dataWalker = function (&$data) use ($callable, $matcher)
        {
            array_walk_recursive($data, $matcher);
            
            if ($callable) {
                $data = $callable($data);
            }
            
            return $data;
        };
        
        parent::__construct($cache, $uid . 'Env-', $dataWalker, $stat);
    }
}
