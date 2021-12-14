<?php
declare(strict_types=1);
namespace srouter\interfaces;

/***
*describes a class that can take input data and return an argument value to
*call a controller method.
*/

interface argument_extractor {

/**
*must extract the argument value (mixed) for the given parameter from the
*request. Uri arguments are provided in case they are needed.
*/
	public function extract(
		\srouter\parameter $_parameter,
		\srouter\interfaces\request $_request,
		array $_uri_arguments
	);
}
