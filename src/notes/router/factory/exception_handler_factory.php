<?php
namespace notes\router\factory;

class exception_handler_factory implements \srouter\interfaces\exception_handler_factory {

	public function build_exception_handler(
		string $_string
	):?\srouter\interfaces\exception_handler {

		return new \notes\router\exception_handler();
	}
}