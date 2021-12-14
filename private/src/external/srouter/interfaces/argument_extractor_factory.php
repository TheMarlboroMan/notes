<?php
declare(strict_types=1);
namespace srouter\interfaces;

/***
*describes a class that can build argument extractors from a string. Accepts a
*null value to signal it cannot build whatever has been requested.
*/

interface argument_extractor_factory {

/**
*must build and return an argument extractor.
*/
	public function build_argument_extractor(string $_name) : ?\srouter\interfaces\argument_extractor;
}
