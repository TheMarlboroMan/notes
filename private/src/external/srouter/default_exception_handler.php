<?php
declare(strict_types=1);
namespace srouter;

/**
*this is the default exception and error handler for the router. It will (in
*order) catch all the srouter\exception exceptions and then anything else.
*To override this behaviour, custom handlers can be created and injected into
*the router or into a particular route.
*/

class default_exception_handler implements \srouter\interfaces\exception_handler {

	use \srouter\traits\strict;

	private const log_module="default_exception_handler";

	public function __construct(
		\log\logger_interface $_logger
	) {

		$this->logger=$_logger;
	}

/**
*handles a exception
*/

	public function handle_exception(
		\Exception $_e,
		?\srouter\interfaces\request $_request,
		?\srouter\route $_route
	) : ?\srouter\http_response {

		if($_e instanceof \srouter\exception\argument_mapping) {

			$this->logger->warning($_e->getMessage()." (".get_class($_e).")", self::log_module);
			return new \srouter\http_response(500, [], "internal server error [i001]");
		}

		if($_e instanceof \srouter\exception\bad_dependency) {

			$this->logger->warning($_e->getMessage()." (".get_class($_e).")", self::log_module);
			return new \srouter\http_response(500, [], "internal server error [i002]");
		}

		if($_e instanceof \srouter\exception\bad_response) {

			$this->logger->warning($_e->getMessage()." (".get_class($_e).")", self::log_module);
			return new \srouter\http_response(500, [], "internal server error [i003]");
		}

		if($_e instanceof \srouter\exception\bad_argument_type) {

			$this->logger->warning($_e->getMessage()." (".get_class($_e).")", self::log_module);
			return new \srouter\http_response(400, [], "bad request [u001]");
		}

		if($_e instanceof \srouter\exception\missing_compulsory_argument) {

			$this->logger->warning($_e->getMessage()." (".get_class($_e).")", self::log_module);
			return new \srouter\http_response(400, [], "bad request [u002]");
		}

		if($_e instanceof \srouter\exception\not_found) {

			$this->logger->warning($_e->getMessage()." (".get_class($_e).")", self::log_module);
			return new \srouter\http_response(404, [], "not found [u003]");
		}

		if($_e instanceof \srouter\exception\bad_request) {

			$this->logger->warning($_e->getMessage()." (".get_class($_e).")", self::log_module);
			return new \srouter\http_response(400, [], "bad request [u004]");
		}

		if($_e instanceof \srouter\exception\unauthorized) {

			$this->logger->warning($_e->getMessage()." (".get_class($_e).")", self::log_module);
			return new \srouter\http_response(401, [], "unauthorized [u004]");
		}

		$this->logger->warning($_e->getMessage()." (".get_class($_e).")", self::log_module);
		return new \srouter\http_response(500, [], "internal server error [e001]");
	}

/**
*handles an error
*/

	public function handle_error(
		\Error $_e,
		?\srouter\interfaces\request $_request,
		?\srouter\route $_route
	) : ?\srouter\http_response {

		$this->logger->warning($_e->getMessage()." (".get_class($_e).", ".$_e->getFile().":".$_e->getLine().") caught by default error handler");
		return new \srouter\http_response(500, [], "internal server error [e002]");
	}

	private \log\logger_interface $logger;
}
