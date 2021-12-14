<?php
declare(strict_types=1);
namespace log;

//!Logger that buffers its contents to a string. Useful when testing
//!systems that must do logging under certain circumstances.
class buffer_logger implements logger_interface {

	private array               $buffer=[];
	private formatter_interface $formatter;

	public function		__construct(
		formatter_interface $_formatter
	) {

		$this->formatter=$_formatter;
	}

	public function get_buffer() {

		return $this->buffer;
	}

	public function clear_buffer() {

		$this->buffer=[];
	}

	//begin implementation of logger_interface
	public function		emergency(string $_message, string $_module = "") : logger_interface
	{

		return $this->send_log(LOG_EMERG, $_message, $_module);
	}

	public function		alert(string $_message, string $_module = "") : logger_interface
	{

		return $this->send_log(LOG_ALERT, $_message, $_module);
	}

	public function		critical(string $_message, string $_module = "") : logger_interface
	{

		return $this->send_log(LOG_CRIT, $_message, $_module);
	}

	public function		error(string $_message, string $_module = "") : logger_interface
	{

		return $this->send_log(LOG_ERR, $_message, $_module);
	}

	public function		warning(string $_message, string $_module = "") : logger_interface
	{

		return $this->send_log(LOG_WARNING, $_message, $_module);
	}

	public function		notice(string $_message, string $_module = "") : logger_interface
	{

		return $this->send_log(LOG_NOTICE, $_message, $_module);
	}

	public function		info(string $_message, string $_module = "") : logger_interface
	{

		return $this->send_log(LOG_INFO, $_message, $_module);
	}

	public function		debug(string $_message, string $_module = "") : logger_interface
	{

		return $this->send_log(LOG_DEBUG, $_message, $_module);
	}

	public function     set_formatter(
		formatter_interface $_formatter
	) : logger_interface{

		$this->formatter = $_formatter;
		return $this;
	}
	//end implementation of logger_interface

	private function 	send_log(
		int $_level,
		string $_message,
		string $_module
	) : buffer_logger {

		$this->buffer[]=$this->formatter->format($_message, $_level, $_module);
		return $this;
	}
}
