<?php
declare(strict_types=1);
namespace sorm\internal;

/**
*this class loads values into entities.
*/

class entity_inflator {

	use \sorm\traits\strict;

	public function __construct(
		\sorm\interfaces\entity_factory $_entity_factory,
		\sorm\interfaces\entity_property_mapper $_property_mapper,
		?\sorm\interfaces\on_default_builder $_on_default_builder,
		?\sorm\interfaces\value_mapper_factory $_value_mapper_factory
	) {

		$this->entity_factory=$_entity_factory;
		$this->property_mapper=$_property_mapper;
		$this->on_default_builder=$_on_default_builder;
		$this->value_mapper_factory=$_value_mapper_factory;
	}

/**
*builds a new entity loaded with default values.
*/
	public function build_entity(
		\sorm\internal\entity_definition $_definition
	) :\sorm\interfaces\entity {

		$entity=$this->entity_factory->build_entity($_definition->get_class_name());
		$this->set_entity_default_values($entity, $_definition);

		if(null!==$this->on_default_builder) {

			$this->on_default_builder->on_default_build($entity);
		}

		return $entity;
	}

/**
*loads the given entity with the data map, which is expressed as storage map
*key to value.
*/

	public function inflate(
		\sorm\interfaces\entity $_entity,
		\sorm\internal\entity_definition $_definition,
		array $_data
	) : \sorm\interfaces\entity {

		$classname=get_class($_entity);

		foreach($_definition as $property) {
			$fieldname=$property->get_field();

			if(array_key_exists($fieldname, $_data)) {


				//TODO: Map this!!
				$value=$_data[$fieldname];
				$this->set_value_to_property($value, $classname, $property, $_entity);
			}
		}

		return $_entity;
	}

/**
*sets the entity default values according to the entity definition.
*/

	private function set_entity_default_values(
		\sorm\interfaces\entity $_entity,
		\sorm\internal\entity_definition $_definition
	) : void {

		$classname=get_class($_entity);

		foreach($_definition as $property) {

			$this->set_value_to_property($property->get_default(), $classname, $property, $_entity);
		}
	}

/**
*sets a value to a property using transformers if needed.
*/

	private function set_value_to_property(
		$_value,
		string $_classname,
		\sorm\internal\entity_definition_property $_property,
		\sorm\interfaces\entity $_entity
	) {

		$value=$_value;

		if(null!==$this->value_mapper_factory && null!==$_property->get_transform_key()) {

			$transform=$this->value_mapper_factory->build_value_mapper($_property->get_transform_key());
			$value=$transform->from_storage($_property->get_transform_method(), $_value);

			if(! (is_scalar($value) || null===$value)) {

				throw new \sorm\exception\value_map("expected scalar value from '".$_property->get_transform_key().":".$_property->get_transform_method()."'");
			}
		}

		$setter=$this->property_mapper->setter_from_property($_classname, $_property);

		if($_property->is_nullable() && null===$value) {

			$_entity->$setter($value);
			return;
		}

		switch($_property->get_type()) {

			case \sorm\types::t_any:
				$_entity->$setter($value);
			break;
			case \sorm\types::t_bool:
				$_entity->$setter((bool)$value);
			break;
			case \sorm\types::t_datetime:
				$_entity->$setter(new \DateTime($value));
			break;
			case \sorm\types::t_double:
				$_entity->$setter((double)$value);
			break;
			case \sorm\types::t_int:
				$_entity->$setter((int)$value);
			break;
			case \sorm\types::t_string:
				$_entity->$setter((string)$value);
			break;
		}
	}

	private \sorm\interfaces\entity_factory         $entity_factory;
	private \sorm\interfaces\entity_property_mapper $property_mapper;
	private ?\sorm\interfaces\on_default_builder    $on_default_builder;
	private ?\sorm\interfaces\value_mapper_factory  $value_mapper_factory;
}
