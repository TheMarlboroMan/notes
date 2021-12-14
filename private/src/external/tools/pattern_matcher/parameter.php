<?php
namespace tools\pattern_matcher;

//TODO: Perhaps we have different classes for different data-types.

//!A parameter is a named, variable part of a pattern. For example, the
//!pattern this/[id:int] has the parameter "id", which will be an integer.
class parameter {

	private $name;
	private $value;

	//!$_n is a string, $_v is mixed.
	public function __construct($_n, $_v) {
		$this->name=$_n;
		$this->value=$_v;
	}

	//!returns the parameter name.
	public function	get_name() {
		return $this->name;
	}

	//!returns the parameter value regardless of its type.
	public function get_value() {
		return $this->value;
	}
}
