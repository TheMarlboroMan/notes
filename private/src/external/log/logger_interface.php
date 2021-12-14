<?php
declare(strict_types=1);
namespace log;

//!Interface that must be implemented by all log classes.
interface logger_interface {

	//!Logs an emergency.
	public function		emergency(string $_message, string $_module="") : logger_interface;

	//!Logs an alert.
	public function		alert(string $_message, string $_module="") : logger_interface;

	//!Logs a critical status report.
	public function		critical(string $_message, string $_module="") : logger_interface;

	//!Logs an error.
	public function		error(string $_message, string $_module="") : logger_interface;

	//!Logs a warning.
	public function		warning(string $_message, string $_module="") : logger_interface;

	//!Logs a notice.
	public function		notice(string $_message, string $_module="") : logger_interface;

	//!Logs a info entry.
	public function		info(string $_message, string $_module="") : logger_interface;

	//!Logs a debug line.
	public function		debug(string $_message, string $_module="") : logger_interface;

	//!Provides a way to set a new formatter. Must return self.
	public function     set_formatter(formatter_interface $_formatter) : logger_interface;
}
