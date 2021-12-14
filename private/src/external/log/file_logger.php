<?php
declare(strict_types=1);
namespace log;

//!Implementation of log_interface, without module support, as it stands to
//!reason that different modules go in different files.
class file_logger implements logger_interface {

	private                     $file_descriptor;	//!<File descriptor where the log entries will be written.
	private formatter_interface $formatter;

	public function		__construct(
		formatter_interface $_formatter,
		string $_filename
	) {

		$this->file_descriptor=fopen($_filename, 'a');
		$this->formatter=$_formatter;
	}

	public function		__destruct() {

		if($this->file_descriptor) {

			fclose($this->file_descriptor);
		}
	}

	//begin implementation of logger_interface
	public function		emergency(string $_message, string $_module="") : logger_interface {

		return $this->send_log(LOG_EMERG, $_message, $_module);
	}

	public function		alert(string $_message, string $_module="") : logger_interface {

		return $this->send_log(LOG_ALERT, $_message, $_module);
	}

	public function		critical(string $_message, string $_module="") : logger_interface {

		return $this->send_log(LOG_CRIT, $_message, $_module);
	}

	public function		error(string $_message, string $_module="") : logger_interface {

		return $this->send_log(LOG_ERR, $_message, $_module);
	}

	public function		warning(string $_message, string $_module="") : logger_interface {

		return $this->send_log(LOG_WARNING, $_message, $_module);
	}

	public function		notice(string $_message, string $_module="") : logger_interface {

		return $this->send_log(LOG_NOTICE, $_message, $_module);
	}

	public function		info(string $_message, string $_module="") : logger_interface {

		return $this->send_log(LOG_INFO, $_message, $_module);
	}

	public function		debug(string $_message, string $_module="") : logger_interface {

		return $this->send_log(LOG_DEBUG, $_message, $_module);
	}

	public function     set_formatter(
		formatter_interface $_formatter
	) : logger_interface{

		$this->formatter=$_formatter;
		return $this;
	}
	//end implementation of logger_interface

	private function 	send_log(
		int $_level,
		string $_message,
		string $_module
	) : file_logger {

		if(!$this->file_descriptor) {

			return $this;
		}

		fwrite($this->file_descriptor, $this->formatter->format($_message, $_level, $_module));
		return $this;
	}
};
