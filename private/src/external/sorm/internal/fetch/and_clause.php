<?php
declare(strict_types=1);
namespace sorm\internal\fetch;

/**
*expresses a sequence of clauses that must all be true.
*/
class and_clause implements \sorm\interfaces\fetch_node {

	use \sorm\traits\strict;

	public function     __construct(
		int $_flags,
		array $_clauses
	) {

		$this->flags=$_flags;
		$this->clauses=$_clauses;
	}

/**
*returns the clause flags.
*/
	public function     get_flags() : int {

		return $this->flags;
	}

/**
*returns the collection of internal clauses.
*/
	public function     get_clauses() : array {

		return $this->clauses;
	}

/**
*implementation of fetch_node.
*/
	public function accept(
		\sorm\interfaces\fetch_translator $_translator
	) : void {

		$_translator->do_and($this);
	}

	private int          $flags;
	private array        $clauses=[];
}
