<?php
declare(strict_types=1);
namespace sorm\internal;

/**
*defines a complete entity and a few characterisicts of how it relates to
*the storage layer. If used as an array, the property definitions are accesed.
*/
class entity_definition implements \Countable, \IteratorAggregate, \ArrayAccess {

	use \sorm\traits\strict;

/**
*returns true if this entity has a primary key defined.
*/
	public function                 has_primary_key() : bool {

		return null !== $this->primary_key_name;
	}

/**
*returns the name of the property that represents the primary key.
*/
	public function                 get_primary_key_name() : ?string {

		return $this->primary_key_name;
	}

/**
*returns the implementation dependant (e.g. table name) name of the storage
*where these entities reside.
*/
	public function                 get_storage_key() : string {

		return $this->storage_key;
	}

/**
*adds a new property to the entity definition.
*/
	public function                 add_property(
		\sorm\internal\entity_definition_property $_prop
	) :\sorm\internal\entity_definition {

		$this->property_map[$_prop->get_property()]=$_prop;
		return $this;
	}
/**
*sets the primary key name.
**/
	public function                 set_primary_key_name(
		string $_val
	) : \sorm\internal\entity_definition {

		$this->primary_key_name=$_val;
		return $this;
	}

/**
*returns the class name, which a fully qualified class name.
*/
	public function                 get_class_name() : string {

		return $this->classname;
	}

/**
*sets the fully qualified classname.
*/
	public function                 set_classname(
		string $_val
	) : \sorm\internal\entity_definition {

		$this->classname=$_val;
		return $this;
	}

/**
*sets the name of the storage key.
*/
	public function                 set_storage_key(
		string $_val
	) : \sorm\internal\entity_definition {

		$this->storage_key=$_val;
		return $this;
	}

//begin countable implementation

	public function                 count() : int {

		return count($this->property_map);
	}
//end countable implementation

//begin iteratoraggregate implementation

	public function                 getIterator() : \ArrayIterator {

		return new \ArrayIterator($this->property_map);
	}

//end iteratoraggregate implementation

//begin arrayaccess implementation

	public function                 offsetExists(
		/*mixed*/ $_offset
	) : bool {

		return array_key_exists($_offset, $this->property_map);
	}

	public function                 offsetGet(
		/*mixed*/ $_offset
	) /*:mixed*/ {

		return $this->property_map[$_offset];
	}

	public function                 offsetSet(
		/*mixed*/ $_offset,
		/*mixed*/ $_value
	) :void {

		throw new \sorm\exception\exception("entity defintion does not support setting offsets");
	}

	public function                 offsetUnset(
		/*mixed*/ $_offset
	) : void {

		throw new \sorm\exception\exception("entity defintion does not support unsetting of offsets");
	}
//end arrayaccess implementation

	private ?string                 $primary_key_name;
	private string                  $classname; //!<Fully qualified classname, no excuses.
	private string                  $storage_key;
	private array                   $property_map; //!< Array of entity_definition_property
}
