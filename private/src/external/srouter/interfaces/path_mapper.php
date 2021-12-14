<?php
declare(strict_types=1);
namespace srouter\interfaces;

/**
*describes the interface that must map an uri and a method to a controller
*name and method, with some optional uri_argument parameters too.
*/

interface path_mapper {

/**
*must map uri and method (HTTP method) to the resulting package. URI is assumed
*to have been transformed by a uri_transformer. Most importantly, must return
*null to signal that it could not match anything. Any uri parameters must be
*provided in the result as uri_argument objects. The most complicated part
*would be mapping whatever the input is to "argument" objects.
*/
	public function map(string $_method, string $_uri) : ?\srouter\route;
}
