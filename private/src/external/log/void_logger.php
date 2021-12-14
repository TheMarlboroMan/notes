<?php
declare(strict_types=1);
namespace log;

//!Implementation of log_interface which does absolutely nothing. Useful when
//!we require a log and we want to log nothing.
class void_logger implements logger_interface {

	//begin implementation of logger_interface
	public function		emergency(string $_message, string $_module="") : logger_interface {return $this;}
	public function		alert(string $_message, string $_module="") : logger_interface {return $this;}
	public function		critical(string $_message, string $_module="") : logger_interface {return $this;}
	public function		error(string $_message, string $_module="") : logger_interface {return $this;}
	public function		warning(string $_message, string $_module="") : logger_interface {return $this;}
	public function		notice(string $_message, string $_module="") : logger_interface {return $this;}
	public function		info(string $_message, string $_module="") : logger_interface {return $this;}
	public function		debug(string $_message, string $_module="") : logger_interface {return $this;}
	public function     set_formatter(formatter_interface $_nothing) : logger_interface {return $this;}
	//end implementation of logger_interface
}
