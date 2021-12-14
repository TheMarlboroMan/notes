<?php
declare(strict_types=1);
namespace sorm\interfaces;

/**
*defines a class that can take a string present in a mapping file and turn it
*into a valid php class name. implementors must provide one of these.
*/

interface entity_name_mapper {

/**
*converts the given string to a valid class name.
*/
	public function map_name(string $_name) : string;
}
