<?php
declare(strict_types=1);
namespace sorm\internal;

/**
*defines a payload value, consisting of a value and its type.
*/
class value {

	use \sorm\traits\strict;

	public function         __construct(
		$_value,
		int $_type //<! one of the sorm\types constants.
	) {

		$this->type=$_type;
		$this->value=$_value;
	}

/**
*returns the value type as expressed in \sorm\types.
*/

	public function         get_type() : int {

		return $this->type;
	}

/**
*returns the value itself, of mixed type.
*/
	public function         get_value() {

		return $this->value;
	}

	private int             $type;
	private                 $value;
}
