<?php
declare(strict_types=1);
namespace sorm;

/**
*acts as an interface between the application and the storage layer. Can
*retrieve, create, update and delete entities. Must be provided with a number
*of implementation-dependant instances to work.
*/
class entity_manager {

	use \sorm\traits\strict;

	public function __construct(
		string $_map_file_path,
		\log\logger_interface $_logger,
		\sorm\interfaces\storage_interface $_storage_interface,
		\sorm\interfaces\entity_factory $_entity_factory,
		\sorm\interfaces\entity_property_mapper $_entity_property_mapper,
		?\sorm\interfaces\value_mapper_factory $_value_mapper_factory,
		?\sorm\interfaces\on_default_builder $_on_default_builder,
		?\sorm\interfaces\entity_name_mapper $_entity_name_mapper
	) {

		$this->logger=$_logger;
		$this->storage_interface=$_storage_interface;
		$this->entity_property_mapper=$_entity_property_mapper;
		$this->entity_name_mapper=$_entity_name_mapper;
		$this->entity_inflator=new \sorm\internal\entity_inflator(
			$_entity_factory,
			$this->entity_property_mapper,
			$_on_default_builder,
			$_value_mapper_factory
		);

		$this->load_map($_map_file_path);
	}

/**
*returns the fetch builder, with which to build fetch actions.
*/
	public function get_fetch_builder() : \sorm\fetch {

		if(null===$this->fetch_builder) {

			$this->fetch_builder=new \sorm\fetch;
		}

		return $this->fetch_builder;
	}

/**
*enables some extra checks to be performed. Nothing to gain from it, the
*application will crash all the same if the tests do not perform well.
*/
	public function enable_extra_checks() : \sorm\entity_manager {

		$this->with_extra_checks=true;
		return $this;
	}

/**
*retrieves a collection of entities from the database.
*/
	public function fetch(
		string $_class,
		\sorm\interfaces\fetch_node $_criteria,
		?\sorm\internal\order_by $_order=null,
		?\sorm\internal\limit_offset $_limit_offset=null
	) : \sorm\interfaces\fetch_collection {

		if(!array_key_exists($_class, $this->definition_map)) {

			throw new \sorm\exception\malformed_setup("entity for '$_class' is not defined");
		}

		return $this->storage_interface->fetch(
			$this->definition_map[$_class],
			$this->entity_inflator,
			$this->value_mapper_factory,
			$_criteria,
			$_order,
			$_limit_offset
		);
	}

/**
*retrieves a single entity from the database.
*/
public function fetch_one(
	string $_class,
	\sorm\interfaces\fetch_node $_criteria,
	?\sorm\internal\order_by $_order=null
) : ?\sorm\interfaces\entity {

	if(!array_key_exists($_class, $this->definition_map)) {

		throw new \sorm\exception\malformed_setup("entity for '$_class' is not defined");
	}

	$collection=$this->storage_interface->fetch(
		$this->definition_map[$_class],
		$this->entity_inflator,
		$this->value_mapper_factory,
		$_criteria,
		$_order,
		new \sorm\internal\limit_offset(1, 0)
	);

	if(!$collection->get_count()) {

		return null;
	}

	$result=$collection->next();
	unset($collection);
	return $result;
}

/**
*alias for fetch_one with the primary key (numeric only!!)
*/
	public function fetch_by_id(
		string $_class,
		int $_id
	) : ?\sorm\interfaces\entity {

		//TODO: The definition map is not checked.

		return $this->fetch_one(
			$_class,
			$this->get_fetch_builder()->equals($this->definition_map[$_class]->get_primary_key_name(), $_id)
		);
	}

/**
*creates a default constructed entity with all values set as default by the
*map file.
*/
	public function build(
		string $_class
	) : \sorm\interfaces\entity {

		if(!array_key_exists($_class, $this->definition_map)) {

			throw new \sorm\exception\malformed_setup("entity for '$_class' is not defined");
		}

		return $this->entity_inflator->build_entity($this->definition_map[$_class]);
	}

/**
*attempts to persist an entity to storage. Rules of persistence are those of
*the underlying storage interface. Returns the created entity.
*/
	public function create(
		\sorm\interfaces\entity $_entity
	) : \sorm\interfaces\entity {

		$classname=get_class($_entity);
		if(!array_key_exists($classname, $this->definition_map)) {

			throw new \sorm\exception\malformed_setup("entity for '$classname' is not defined");
		}

		$definition=$this->definition_map[$classname];

		//prepare the payload.
		$payload=new \sorm\internal\payload($definition);
		foreach($definition as $property) {

			$getter=$this->entity_property_mapper->getter_from_property($classname, $property);

			if($this->with_extra_checks && !method_exists($_entity, $getter)) {

				throw new \sorm\exception\malformed_setup("entity ".get_class($_entity)." does not implement getter ".$getter);
			}

			$value=$_entity->$getter();

			if(null!==$this->value_mapper_factory && null!==$property->get_transform_key()) {

				$transform=$this->value_mapper_factory->build_value_mapper($property->get_transform_key());
				$value=$transform->from_storage($property->get_transform_value(), $value);

				if(! (is_scalar($value) || null===$value)) {

					throw new \sorm\exception\value_map("expected scalar value from '".$property->get_transform_key().":".$property->get_transform_value()."'");
				}
			}
			//this actually allows you to specify a primary key

			$payload->add(
				$property->get_property(),
				new \sorm\internal\value($value, $property->get_type())
			);
		}

		try {

			//Send the payload, will return a primary key value...
			$pk_value=$this->storage_interface->create($payload);
			$pk_name=$definition->get_primary_key_name();

			//Set the primary key value.
			$setter=$this->entity_property_mapper->setter_from_property($classname, $definition[$pk_name]);
			if($this->with_extra_checks && !method_exists($_entity, $setter)) {

				throw new \sorm\exception\malformed_setup("entity ".get_class($_entity)." does not implement setter ".$setter);
			}

			//assumed to be an integer.
			$_entity->$setter((int)$pk_value->get_value());

			return $_entity;
		}
		catch(\Exception $e) {

			throw new \sorm\exception\create_error("could not create entity: ".$e->getMessage());
		}
		catch(\Error $e) {

			throw new \sorm\exception\create_error("could not create entity: ".$e->getMessage());
		}
	}

/**
*attemps to update an entity. Rules of persistence are those of the underlying
*storage. Returns the updated entity
*/
	public function update(
		\sorm\interfaces\entity $_entity
	) : \sorm\interfaces\entity {

		$classname=get_class($_entity);
		if(!array_key_exists($classname, $this->definition_map)) {

			throw new \sorm\exception\malformed_setup("entity for '$classname' is not defined");
		}

		$definition=$this->definition_map[$classname];
		$pk_name=$definition->get_primary_key_name();

		//prepare the payload.
		$payload=new \sorm\internal\payload($definition);

		foreach($definition as $property) {

			$getter=$this->entity_property_mapper->getter_from_property($classname, $property);
			if($this->with_extra_checks && !method_exists($_entity, $getter)) {

				throw new \sorm\exception\malformed_setup("entity ".get_class($_entity)." does not implement getter ".$getter);
			}

			$value=$_entity->$getter();

			if(null!==$this->value_mapper_factory && null!==$property->get_transform_key()) {

				$transform=$this->value_mapper_factory->build_value_mapper($property->get_transform_key());
				$value=$transform->from_storage($property->get_transform_value(), $value);

				if(! (is_scalar($value) || null===$value)) {

					throw new \sorm\exception\value_map("expected scalar value from '".$property->get_transform_key().":".$property->get_transform_value()."'");
				}
			}

			if($pk_name===$property->get_property()) {

				$payload->set_primary_key(
					new \sorm\internal\value($value, $property->get_type())
				);
			}

			$payload->add(
				$property->get_property(),
				new \sorm\internal\value($value, $property->get_type())
			);
		}

		try {

			$this->storage_interface->update($payload);
			return $_entity;
		}
		catch(\Exception $e) {

			throw new \sorm\exception\update_error("could not update entity: ".$e->getMessage());
		}
		catch(\Error $e) {

			throw new \sorm\exception\update_error("could not update entity: ".$e->getMessage());
		}
	}

/**
*attemps to delete an entity. Rules of persistence are those of the underlying
*storage. Returns the deleted entity, which will still have the same state
*as it had.
*/
	public function delete(
		\sorm\interfaces\entity $_entity
	) : \sorm\interfaces\entity {

		$classname=get_class($_entity);
		if(!array_key_exists($classname, $this->definition_map)) {

			throw new \sorm\exception\malformed_setup("entity for '$classname' is not defined");
		}

		$definition=$this->definition_map[$classname];
		$pk_name=$definition->get_primary_key_name();

		//prepare the payload.
		$payload=new \sorm\internal\payload($definition);
		foreach($definition as $property) {

			//disallow updating of the primary key for the puerile reason that
			//it would make the payload confusing with "old id" and "new id".
			if($pk_name!==$property->get_property()) {

				continue;
			}

			$getter=$this->entity_property_mapper->getter_from_property($classname, $property);
			if($this->with_extra_checks && !method_exists($_entity, $getter)) {

				throw new \sorm\exception\malformed_setup("entity ".get_class($_entity)." does not implement getter ".$getter);
			}

			$payload->set_primary_key(
				new \sorm\internal\value($_entity->$getter(), $property->get_type())
			);
		}

		try {

			$this->storage_interface->delete($payload);
			return $_entity;
		}
		catch(\Exception $e) {

			throw new \sorm\exception\delete_error("could not update entity: ".$e->getMessage());
		}
		catch(\Error $e) {

			throw new \sorm\exception\delete_error("could not update entity: ".$e->getMessage());
		}
	}

/**
*performs internal initialization.
*/
	private function load_map(
		string $_filename
	) {

		$loader=new \sorm\internal\map_loader($this->logger, $this->entity_name_mapper);
		$loader->load($_filename, $this->definition_map);
	}

	private \log\logger_interface                   $logger;
	private \sorm\interfaces\storage_interface      $storage_interface;
	private bool                                    $with_extra_checks=false;
	private \sorm\interfaces\entity_property_mapper $entity_property_mapper;
	private ?\sorm\interfaces\entity_name_mapper    $entity_name_mapper;
	private ?\sorm\fetch                            $fetch_builder=null;
	private ?\sorm\interfaces\value_mapper_factory  $value_mapper_factory=null;
	private \sorm\internal\entity_inflator          $entity_inflator;
	private array                                   $definition_map=[]; //!<Map of fully qualified classname to entity_definition.
}
