<?php
namespace notes\orm;

class entity_property_mapper implements \sorm\interfaces\entity_property_mapper {

	public function getter_from_property(
		string $_classname,
		\sorm\internal\entity_definition_property $_prop
	) : string {

		return "set_".$_prop->get_property();
	}

	public function setter_from_property(
		string $_classname,
		\sorm\internal\entity_definition_property $_prop)
	: string {

		return $_prop->get_type() === \sorm\types::t_bool
			? "is_".$_prop->get_property()
			: "get_".$_prop->get_property();
	}
}