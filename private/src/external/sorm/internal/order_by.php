<?php
declare(strict_types=1);
namespace sorm\internal;

/**
*expresses that the results of a fetch operation must be ordered in a specific
*manner, consisting on a sequence of order clauses.
*/

class order_by implements \IteratorAggregate{

	use \sorm\traits\strict;

	public function __construct(
		array $_order_nodes
	) {

		$this->order_nodes=$_order_nodes;
	}

/**
*returns true if any order clause is specified.
*/

	public function has_order() : bool {

		return (bool)count($this->order_nodes);
	}

//begin iteratoraggregate implementation

	public function                 getIterator() : \ArrayIterator {

		return new \ArrayIterator($this->order_nodes);
	}

//end iteratoraggregate implementation


	private array $order_nodes=[];
}
