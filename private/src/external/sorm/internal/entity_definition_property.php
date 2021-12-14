<?php
declare(strict_types=1);
namespace sorm\internal;

/**
*defines an entity property. most of its values have to do with how the property
*works in php, wich a few exceptions.
*/
class entity_definition_property {

	use \sorm\traits\strict;

	public function __construct(
		string $_property,
		string $_field,
		int $_type,
		bool $_nullable,
		$_default,
		?string $_transform_key,
		?string $_transform_method
	) {

		$this->property=$_property;
		$this->field=$_field;
		$this->type=$_type;
		$this->nullable=$_nullable;
		$this->default=$_default;
		$this->transform_key=$_transform_key;
		$this->transform_method=$_transform_method;
	}

/**
*returns the name of the entity property
*/
	public function get_property() : string {

		return $this->property;
	}

/**
*returns the storage name (implementation dependant, like a column row) for
*this property.
*/
	public function get_field() : string {

		return $this->field;
	}

/**
*returns any of the \sorm\types constants indicating the type this property is
*expected to be.
*/
	public function get_type() : int {

		return $this->type;
	}

/**
*returns true if the property can be null.
*/
	public function is_nullable() : bool {

		return $this->nullable;
	}

/**
*returns any value that can be considered as the default value for this property
*/
	public function get_default() {

		return $this->default;
	}

/**
*if there are transformations to be carried out between the storage layer and
*the application layer regarding the values of this property, this indicates
*the implementation dependant name of the transformer that must be used.
*/

	public function get_transform_key() :?string {

		return $this->transform_key;
	}

/**
*if there are transformations to be carried out between the storage layer and
*the application layer regarding the values of this property, this indicates
*the implementation dependant name of the transformer method that must be
*called.
*/
	public function get_transform_method() :?string {

		return $this->transform_method;
	}

	private string              $property;
	private string              $field;
	private int                 $type;
	private bool                $nullable;
	private                     $default;
	private                     $transform_key;
	private                     $transform_method;
}
