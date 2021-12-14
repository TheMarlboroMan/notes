<?php
declare(strict_types=1);
namespace sorm\internal;

/**
*this class takes a map file and sets up entity definitions from it.
*/
class map_loader {

	use \sorm\traits\strict;

	public function __construct(
		\log\logger_interface $_logger,
		?\sorm\interfaces\entity_name_mapper $_entity_name_mapper
	) {

		$this->logger=$_logger;
		$this->entity_name_mapper=$_entity_name_mapper;
	}

/**
*loads entity definitions from the filename into the target map, in which the
*fully qualified class name will be used as a key.
*/

	public function load(
		string $_filename,
		array &$_target
	) {

		//could be done without files, but let's be opinionated for now.
		if(!file_exists($_filename)) {

			throw new \sorm\exception\map_loader_error("cannot load map file '$_filename': file does not exist or is not readable");
		}

		$contents=file_get_contents($_filename);
		if(false===$contents) {

			throw new \sorm\exception\map_loader_error("cannot load map file '$_filename': failed to read file contents");
		}

		$json=json_decode($contents);
		if(JSON_ERROR_NONE !== json_last_error()) {

			throw new \sorm\exception\map_loader_error("cannot load map file '$_filename': failed to decode file contents (".json_last_error_msg().")");
		}

		try {

			$this->check_property($json, "entities", self::check_array);
			foreach($json->entities as $entity_node) {


				$entity=$this->build_entity_definition($entity_node);

				if(array_key_exists($entity->get_class_name(), $_target)) {

					throw new \sorm\exception\map_loader_error("definition for '".$entity->get_class_name()."' has already been given!");
				}

				$_target[$entity->get_class_name()]=$entity;
			}
		}
		catch(\sorm\exception\map_loader_error $e) {

			throw new \sorm\exception\map_loader_error("could not build entity definition: ".$e->getMessage());
		}
	}

/**
*infers an entity classname from the value exposed in a map file
*/

	private function infer_classname(
		\stdClass $_node
	) : string {

		$this->check_property($_node, "entity", self::check_string);
		$entity_name=$_node->entity;

		return null===$this->entity_name_mapper
			? $entity_name
			: $this->entity_name_mapper->map_name($entity_name);
	}

/**
*internal method, builds a complete entity definition.
*/

	private function build_entity_definition(
		\stdClass $_node
	) : \sorm\internal\entity_definition {

		$def=new \sorm\internal\entity_definition();
		$def->set_classname($this->infer_classname($_node));

		$this->check_property($_node, "storage_key", self::check_string);
		$def->set_storage_key($_node->storage_key);

		$this->check_property($_node, "composition", self::check_array);
		foreach($_node->composition as $property_node) {

			$property=$this->build_entity_definition_property($property_node);

			if(property_exists($property_node, "primary")
				&& $property_node->primary
			) {

				$def->set_primary_key_name($property->get_property());
			}

			$def->add_property($property);
		}

		return $def;
	}

/**
*internal method, builds a complete entity property.
*/

	private function build_entity_definition_property(
		\stdClass $_node
	) : \sorm\internal\entity_definition_property {

		$this->check_property($_node, "property", self::check_string);
		$this->check_property($_node, "field", self::check_string);
		$this->check_property($_node, "type", self::check_string);
		$this->check_property($_node, "nullable", self::check_bool);
		$this->check_property($_node, "default", self::check_none);

		$type=$type=\sorm\types::t_any;
		switch($_node->type) {

			case "any": $type=\sorm\types::t_any; break;
			case "bool": $type=\sorm\types::t_bool; break;
			case "datetime": $type=\sorm\types::t_datetime; break;
			case "double": $type=\sorm\types::t_double; break;
			case "string": $type=\sorm\types::t_string; break;
			case "int": $type=\sorm\types::t_int; break;
			default:
				throw new \sorm\exception\map_loader_error("unknown property type '".$_node->type."'");
		}

		$transform_key=null;
		$transform_method=null;

		if(property_exists($_node, "transform")) {

			$this->check_property($_node, "transform", self::check_string);
			if(1 !== substr_count($_node->transform, ":")) {
				throw new \sorm\exception\map_loader_error("malformed transform type, must be key:method");
			}

			list($transform_key, $transform_method)=explode(":", $_node->transform);
		}

		return new \sorm\internal\entity_definition_property(
			$_node->property,
			$_node->field,
			$type,
			$_node->nullable,
			$_node->default,
			$transform_key,
			$transform_method
		);
	}

/**
*checks if the property exists and matches the given type. Throws on failure.
*/

	private const check_none=0;
	private const check_array=1;
	private const check_string=2;
	private const check_bool=3;
	private function check_property(
		\stdClass $_doc,
		string $_name,
		int $_check_type
	) {

		if(!property_exists($_doc, $_name)) {

			throw new \sorm\exception\map_loader_error("missing property '$_name'");
		}

		switch($_check_type) {

			case self::check_none: return;
			case self::check_array:
				if(!is_array($_doc->$_name)) {

					throw new \sorm\exception\map_loader_error("property '$_name' is expected to be an array");
				}
				return;
			case self::check_string:
				if(!is_string($_doc->$_name)) {

					throw new \sorm\exception\map_loader_error("property '$_name' is expected to be a string");
				}
				return;
			case self::check_bool:
				if(!is_bool($_doc->$_name)) {

					throw new \sorm\exception\map_loader_error("property '$_name' is expected to be a boolean");
				}
				return;

		}
	}

	private \log\logger_interface               $logger;
	private ?\sorm\interfaces\entity_name_mapper $entity_name_mapper;
}

