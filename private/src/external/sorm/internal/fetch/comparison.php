<?php
declare(strict_types=1);
namespace sorm\internal\fetch;

/**
*defines a clause that can be used to perform numeric comparisons, as well as
*basic string equality.
*/
class comparison implements \sorm\interfaces\fetch_node {

	use \sorm\traits\strict;

	public function     __construct(
		int $_flags,
		string $_property,
		$_value
	) {

		$this->flags=$_flags;
		$this->property=$_property;
		$this->value=$_value;
	}

/**
*returns the clause flags.
*/
	public function     get_flags() : int {

		return $this->flags;
	}

/**
*returns the entity property name against which the value will be tested.
*/
	public function     get_property() : string {

		return $this->property;
	}

/**
*returns the value to be tested.
*/
	public function     get_value() {

		return $this->value;
	}

/**
*implementation of fetch_node.
*/
	public function accept(
		\sorm\interfaces\fetch_translator $_translator
	) : void {

		$_translator->do_comparison($this);
	}

	private int             $flags;
	private string          $property;
	private                 $value;
}
