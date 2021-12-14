<?php
declare(strict_types=1);
namespace srouter\interfaces;

/**
*describes a class that can build exception handlers. Accepts null to signify
*that it can't build something.
*/

interface exception_handler_factory {

/**
*must build and return a exception handler.
*/
	public function build_exception_handler(string $_name) : ?\srouter\interfaces\exception_handler;
}
