<?php

namespace Princeton\Adapter;

/*	
	PHP version of JSONT.js:
			This work is licensed under Creative Commons GNU LGPL License.
			License: http://creativecommons.org/licenses/LGPL/2.1/
		    Version: 0.9
			Author:  Stefan Goessner/2006
			Web:     http://goessner.net/ 
*/
class JSONT {
	public $allow_functions = false;
	public $output = false;
	public $rules;
	
	public function __construct($rules)
	{
		$this->rules = $rules;
		foreach (($this->rules) as $rule => $expr) {
			if (substr($rule, 0, 5) !== '$self') {
				$this->rules['$self->' . $rule] = $expr;
			}
		}
		var_dump($this->rules);
	}
	
	public function transform($self)
	{
		return $this->apply('$self', $self);
	}
	
	private function trf($s, $expr, $self)
	{
		return preg_replace('/{([A-Za-z0-9_\$\.\[\]\'@\(\)]+)}/',
			// TODO How to do this in PHP???
			function ($a0,$a1){ return $this->processArg($a1, $expr, $self);},
			$s);
	}
	
	public function apply($expr, $self)
	{
		$x = preg_replace('/\[[0-9]+\]/', "[*]", $expr);
		$res = null;
		if (isset($this->rules[$x])) {
			echo "found rule $x\n";
			if (is_string($this->rules[$x])) {
				if ($this->allow_functions && preg_match('/^function(.*) *{.*}$/', $this->rules[$x])) {
					$f = eval($this->rules[$x]);
					$res = $this->trf((string)($f(json_decode($expr))), $expr, $self);
				} else {
					echo "using rule $x\n";
					$res = $this->trf($this->rules[$x], $expr, $self);
					echo "got $res\n";
				}
			}
		} else {
			$res = $this->evaluate($expr, $self);
		}
		return $res;
	}
	
	public function processArg($arg, $parentExpr, $self)
	{
		$res = "";
		$this->output = true;
		if ($arg[0] == "@") {
			$res = json_decode(preg_replace('/@([A-za-z0-9_]+)\(([A-Za-z0-9_\$\.\[\]\']+)\)/',
				function ($a0,$a1,$a2) {return "\$this->rules['\$self->" . $a1 . "'](" . $this->expand($a2, $parentExpr) . ")";},
				$arg));
         } elseif (arg != "$") {
            $res = $this->apply($this->expand($arg, $parentExpr), $self);
         } else {
            $res = $this->evaluate($parentExpr, $self);
         }
         $this->output = false;
         return $res;
	}
	
	public function evaluate($expr, $self)
	{
		echo "evaluate called for $expr\n";
		$v = json_decode($expr); // refs $self!
		$res = "";
		if (isset($v)) {
			if (is_array($v)) {
				for ($i=0; $i < strlen($v); $i++) {
					if (isset($v[$i])) {
						$res .= $this->apply($expr . "[" . $i . "]", $self);
					}
				}
			} elseif (is_object($v) && $v !== null) {
				foreach ($v as $m) {
					if (isset($v[$m])) {
						$res .= $this->apply($expr . "->" . $m, $self);
					}
				}
			} else if ($this->output) {
				$res .= $v;
			}
		}
		return $res;
	}
	
	public function expand($a, $e)
	{
		return (($e = substr(preg_replace('/^\$/', $e, $a), 0, 5)) != '$self') ? ('$self->' . $e) : $e;
	}
}
