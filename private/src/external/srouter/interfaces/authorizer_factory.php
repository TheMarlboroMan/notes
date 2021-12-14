<?php
declare(strict_types=1);
namespace srouter\interfaces;

/***
*describes a class that can build authorizer from a string. Accepts returning a
*new value if nothing can be built with the given name.
*/

interface authorizer_factory {

/**
*must build and return an authorizer.
*/
	public function build_authorizer(string $_name) : ?\srouter\interfaces\authorizer;
}
