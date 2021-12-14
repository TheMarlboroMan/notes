<?php
declare(strict_types=1);
namespace sorm\interfaces;

/**
*defines a class that must be able to build entities.
*/

interface entity_factory {

/**
*builds an entity from the given string, assumed to be a classname or related
*to it.
*/
	public function build_entity(string $_class) : \sorm\interfaces\entity;
}
