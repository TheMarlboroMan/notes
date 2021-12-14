<?php
declare(strict_types=1);
namespace srouter\interfaces;

/***
*describes a class that can build in transformers from a string. Accepts
*returning null if nothing can be built with the given name.
*/

interface in_transformer_factory {

/**
*must build an input transformer factory.
*/
	public function build_in_transformer(string $_name) : ?\srouter\interfaces\in_transformer;
}
