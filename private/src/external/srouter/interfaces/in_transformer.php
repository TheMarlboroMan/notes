<?php
declare(strict_types=1);
namespace srouter\interfaces;

/**
*describes a class that can take the request and transform it into another.
*/
interface in_transformer {

/**
*must return the transformed request from the input.
*/
	public function transform(\srouter\interfaces\request $_request) : \srouter\interfaces\request;
}
