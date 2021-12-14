<?php
declare(strict_types=1);
namespace sorm\interfaces;

/**
*defines a class that acts as a criteria for entity fetching... it actually
*defines a part of a visitor pattern.
*/

interface fetch_node {

/**
*must call one of the available methods on the fetch_translator interface
*passing itself as the parameter.
*/
	public function accept(\sorm\interfaces\fetch_translator $_translator) : void;
}
