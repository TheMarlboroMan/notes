<?php
declare(strict_types=1);
namespace sorm\interfaces;

/**
*defines a class that can perform actions on entities that have been built with
*default values by the entity manager.
*/
interface on_default_builder {

/**
*must act upon the entity that has been just built with default values.
*/
	public function on_default_build(
		\sorm\interfaces\entity $_entity
	) : void;
}
