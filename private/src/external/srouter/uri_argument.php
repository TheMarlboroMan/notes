<?php
declare(strict_types=1);
namespace srouter;

/**
*uri argument are a very particular thing and take the form of variable
*sections in a uri like /path/user/USERNAME/profile where USERNAME is what
*we call a uri_argument. A uri may have several placeholders and it is the
*job of the path_mapper to uniquely identify each one with a name and assign
*its value.
*/

class uri_argument {

	use \srouter\traits\strict;

	public function __construct(
		string $_name,
		$_value
	) {

		$this->name=$_name;
		$this->value=$_value;
	}

/**
*returns the argument name.
*/
	public function     get_name() :string {

		return $this->name;
	}

/**
*returns the argument value as seen in the URI.
*/
	public function     get_value() {

		return $this->value;
	}

	private string   $name;
	private          $value;
}
