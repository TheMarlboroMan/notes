<?php
declare(strict_types=1);
namespace sorm\interfaces;

/**
*defines a class that, given a fully qualified class name and an entity
*property definition, can return the name of the setter and getter methods.
*/

interface entity_property_mapper {

/**
*must return the getter method name from the given property.
*/
	public function getter_from_property(string $_classname, \sorm\internal\entity_definition_property $_prop) : string;

/**
*must return the setter method name from the given property.
*/
	public function setter_from_property(string $_classname, \sorm\internal\entity_definition_property $_prop) : string;
}
