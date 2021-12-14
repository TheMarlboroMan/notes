<?php
declare(strict_types=1);
namespace srouter\interfaces;

/**
*describes a class that can take a router response and transform it before it
*can be sent out.
*/

interface out_transformer {

/**
*must transform the controller response into an http_response and return it.
*/
	public function transform(\srouter\controller_response $_response) : \srouter\http_response;
}
