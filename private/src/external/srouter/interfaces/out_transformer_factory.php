<?php
declare(strict_types=1);
namespace srouter\interfaces;

/***
*describes a class that can build out transformers from a string. Accepts
*returning null values if nothing can be built from the name.
*/

interface out_transformer_factory {

/**
*must build an output transformer factory.
*/
	public function build_out_transformer(string $_name) : ?\srouter\interfaces\out_transformer;
}
