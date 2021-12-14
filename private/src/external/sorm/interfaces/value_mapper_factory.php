<?php
declare(strict_types=1);
namespace sorm\interfaces;

/**
*defines a class that can build value mappers.
*/

interface value_mapper_factory {

/**
*must build a value mapper from the given key, which is assumed to be related
*in an implementation dependant manner to the mapper itself.
*/
	public function build_value_mapper(string $_key) : \sorm\interfaces\value_mapper;
}
