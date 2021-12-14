<?php
declare(strict_types=1);
namespace sorm\internal\fetch;

/**
*expresses a clause in which a value must be inside of another list of values.
*/
class in implements \sorm\interfaces\fetch_node {

	use \sorm\traits\strict;

	public function     __construct(
		int $_flags,
		string $_property,
		array $_values
	) {

		$this->flags=$_flags;
		$this->property=$_property;
		$this->values=$_values;
	}

/**
*returns the clause flags.
*/
	public function     get_flags() : int {

		return $this->flags;
	}

/**
*returns the entity property name against which the values will be tested.
*/
	public function     get_property() : string {

		return $this->property;
	}

/**
*returns the values to be tested.
*/
	public function     get_values() : array {

		return $this->values;
	}

/**
*implementation of fetch_node.
*/
	public function accept(
		\sorm\interfaces\fetch_translator $_translator
	) : void {

		$_translator->do_in($this);
	}

	private int             $flags;
	private string          $property;
	private array           $values;
}
