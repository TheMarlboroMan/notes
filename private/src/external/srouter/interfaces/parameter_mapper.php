<?php
declare(strict_types=1);
namespace srouter\interfaces;

/**
*describes a class that can take a parameter name (from the class
*\srouter\parameter and then convert it to an parameter name as declared in the
*controller's PHP source.
*/

interface parameter_mapper {

/**
*must take the property "name" from a \srouter\parameter class and turn it into
*the parameter name of a method as declared in the controller source.
*/
	public function map(string $_name) : string;
}
