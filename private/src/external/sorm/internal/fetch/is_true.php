<?php
declare(strict_types=1);
namespace sorm\internal\fetch;

/**
*expresses a clause in which a given property can be evaluated as a boolean
*value.
*/
class is_true implements \sorm\interfaces\fetch_node {

	use \sorm\traits\strict;

	public function     __construct(
		int $_flags,
		string $_property
	) {

		$this->flags=$_flags;
		$this->property=$_property;
	}

/**
*returns the clause flags.
*/
	public function     get_flags() : int {

		return $this->flags;
	}

/**
*returns the entity property name which will be tested as a boolean.
*/
	public function     get_property() : string {

		return $this->property;
	}

/**
*implementation of fetch_node.
*/
	public function accept(
		\sorm\interfaces\fetch_translator $_translator
	) : void {

		$_translator->do_is_true($this);
	}

	private int             $flags;
	private string          $property;
}
