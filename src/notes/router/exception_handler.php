<?php
namespace notes\router;

class exception_handler implements \srouter\interfaces\exception_handler {

	public function handle_exception(
		\Exception $_e,
		?\srouter\interfaces\request $_request,
		?\srouter\route $_route
	) : ?\srouter\http_response {

		if($_e instanceof \srouter\exception\unauthorized) {

			//TODO: a bit dirty with the headers and shit. Maybe we could grab
			//the json out transformer here and let it do the very same magic.
			return new \srouter\http_response(
				401,
				[new \srouter\http_response_header("content-type", "application/json;charset=utf8")],
				json_encode("unauthorized")
			);
		}

		return null;
	}

	public function handle_error(
		\Error $_e,
		?\srouter\interfaces\request $_request,
		?\srouter\route $_route
	) : ?\srouter\http_response {

		return null;
	}


}