<?php
declare(strict_types=1);
namespace log;

/**
*groups more than one logger under a single entity. formatters
*that are set here will be set for all loggers under it so call order is
*critical.
*/

class multicast implements logger_interface {

	private array       $loggers=[];

	public function     add(logger_interface $_logger) {

		$this->loggers[]=$_logger;
		return $this;
	}

	//begin implementation of logger_interface
	public function		emergency(string $_message, string $_module="") : logger_interface{

		foreach($this->loggers as $logger) {

			$logger->emergency($_message, $_module);
		}

		return $this;
	}

	public function		alert(string $_message, string $_module="") : logger_interface{

		foreach($this->loggers as $logger) {

			$logger->alert($_message, $_module);
		}

		return $this;
	}

	public function		critical(string $_message, string $_module="") : logger_interface{

		foreach($this->loggers as $logger) {

			$logger->critical($_message, $_module);
		}

		return $this;
	}

	public function		error(string $_message, string $_module="") : logger_interface{

		foreach($this->loggers as $logger) {

			$logger->error($_message, $_module);
		}

		return $this;
	}

	public function		warning(string $_message, string $_module="") : logger_interface{

		foreach($this->loggers as $logger) {

			$logger->warning($_message, $_module);
		}

		return $this;
	}

	public function		notice(string $_message, string $_module="") : logger_interface{

		foreach($this->loggers as $logger) {

			$logger->notice($_message, $_module);
		}

		return $this;
	}

	public function		info(string $_message, string $_module="") : logger_interface{

		foreach($this->loggers as $logger) {

			$logger->info($_message, $_module);
		}

		return $this;
	}

	public function		debug(string $_message, string $_module="") : logger_interface{

		foreach($this->loggers as $logger) {

			$logger->debug($_message, $_module);
		}

		return $this;
	}

	public function     set_formatter(
		formatter_interface $_formatter
	) : logger_interface {

		foreach($this->loggers as $logger) {

			$logger->set_formatter($_formatter);
		}

		return $this;
	}
	//end implementation of logger_interface
};
