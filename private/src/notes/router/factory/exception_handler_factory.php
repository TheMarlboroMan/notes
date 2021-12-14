<?php
namespace notes\router\factory;

class exception_handler_factory implements \srouter\interfaces\exception_handler_factory {

	public function __construct(
		\log\logger_interface $_logger,
		bool $_verbose_errors
	) {
		$this->logger=$_logger;
		$this->verbose_errors=$_verbose_errors;
	}

	public function build_exception_handler(
		string $_string
	):?\srouter\interfaces\exception_handler {

		return new \notes\router\exception_handler(
			$this->logger,
			$this->verbose_errors
		);
	}

	private \log\logger_interface   $logger;
	private bool                    $verbose_errors;
}