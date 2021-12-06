<?php
namespace notes\router;

class exception_handler implements \srouter\interfaces\exception_handler {

	const log_module="exception_handler";

	public function __construct(
		\log\logger_interface $_logger,
		bool $_verbose_errors
	) {

		$this->logger=$_logger;
		$this->verbose_errors=$_verbose_errors;
	}

	public function handle_exception(
		\Exception $_e,
		?\srouter\interfaces\request $_request,
		?\srouter\route $_route
	) : ?\srouter\http_response {

		if($this->verbose_errors) {

			$this->logger->error($_e->getMessage()." - ".$_e->getTraceAsString(), self::log_module);
		}

		if($_e instanceof \srouter\exception\not_found) {

			//TODO: a bit dirty with the headers and shit. Maybe we could grab
			//the json out transformer here and let it do the very same magic.
			return new \srouter\http_response(
				404,
				[new \srouter\http_response_header("content-type", "application/json;charset=utf8")],
				json_encode("not found")
			);
		}

		if($_e instanceof \srouter\exception\bad_request) {

			return new \srouter\http_response(
				400,
				[new \srouter\http_response_header("content-type", "application/json;charset=utf8")],
				json_encode("bad request")
			);
		}

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

		if($this->verbose_errors) {

			$this->logger->error($_e->getMessage()." - ".$_e->getTraceAsString(), self::log_module);
		}

		return null;
	}

	private \log\logger_interface   $logger;
	private bool                    $verbose_errors;
}