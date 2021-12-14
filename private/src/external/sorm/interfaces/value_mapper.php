<?php
declare(strict_types=1);
namespace sorm\interfaces;

/**
*defines a class that can convert values back and forth from the application
*and storage layers. All values must be expressable in terms of scalars.
*/

interface value_mapper {

/**
*must convert $_value to a scalar as expected by the application layer using
*the $_type transformation, which is implementation dependant.
*/
	public function from_storage(string $_type, $_value);

/**
*must convert $_value to a scalar as expected by the storage layer using
*the $_type transformation, which is implementation dependant.
*/
	public function to_storage(string $_type, $_value);
}
