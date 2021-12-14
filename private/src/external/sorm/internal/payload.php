<?php
declare(strict_types=1);
namespace sorm\internal;

/**
*a payload that will be sent to the storage layer with data needed to perform
*create, update and delete operations. May include a collection of values
*to be set.
*/
class payload implements \Countable, \IteratorAggregate, \ArrayAccess {

	use \sorm\traits\strict;

	public function             __construct(
		\sorm\internal\entity_definition $_definition
	) {

		$this->entity_definition=$_definition;
	}

/**
*returns the entity definition that corresponds to this payload (that is,
*the payload corresponds to a given entity).
*/
	public function             get_entity_definition() : \sorm\internal\entity_definition {

		return $this->entity_definition;
	}

/**
*sets the primary key (as a value) for update and delete operations.
*/
	public function             set_primary_key(
		\sorm\internal\value $_value
	) : \sorm\internal\payload {

		$this->primary_key=$_value;
		return $this;
	}

/**
*returns the primary key for update and delete operations.
*/
	public function             get_primary_key() : ?\sorm\internal\value {

		return $this->primary_key;
	}

/**
*adds a new value to the payload (e.g. a new field to be updated).
*/
	public function             add(
		string $_key,
		\sorm\internal\value $_value
	) : \sorm\internal\payload {

		if(array_key_exists($_key, $this->data_map)) {

			throw new \sorm\exception\internal("duplicate key '$_key' in payload");
		}

		$this->data_map[$_key]=$_value;
		return $this;
	}

	//begin countable implementation

	public function                 count() : int {

		return count($this->data_map);
	}
//end countable implementation

//begin iteratoraggregate implementation

	public function                 getIterator() : \ArrayIterator {

		return new \ArrayIterator($this->data_map);
	}

//end iteratoraggregate implementation

//begin arrayaccess implementation

	public function                 offsetExists(
		/*mixed*/ $_offset
	) : bool {

		return array_key_exists($_offset, $this->data_map);
	}

	public function                 offsetGet(
		/*mixed*/ $_offset
	) /*:mixed*/ {

		return $this->data_map[$_offset];
	}

	public function                 offsetSet(
		/*mixed*/ $_offset,
		/*mixed*/ $_value
	) :void {

		throw new \sorm\exception\exception("payload does not support setting offsets");
	}

	public function                 offsetUnset(
		/*mixed*/ $_offset
	) : void {

		throw new \sorm\exception\exception("payload does not support unsetting of offsets");
	}
//end arrayaccess implementation

	private \sorm\internal\entity_definition    $entity_definition;
	private ?\sorm\internal\value               $primary_key;
	private array                               $data_map=[];
}
