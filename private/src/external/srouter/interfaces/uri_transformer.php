<?php
declare(strict_types=1);
namespace srouter\interfaces;

/**
*describes the interface that must take a complete uri and transform it into
*whatever a path_mapper expects.
*/

interface uri_transformer {

/**
*must map the uri to something useful for the path_mapper.
*/
	public function transform(string $_uri) : string;
}
