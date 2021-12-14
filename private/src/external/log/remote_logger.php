<?php
declare(strict_types=1);
namespace log;

//!Implementation of log interface that makes use or remote syslog
//!capabilities. Depends on the constants LOG_* used by PHP.
class remote_syslog_logger implements logger_interface {

	private string                  $hostname;
	private	int                     $port;
	private formatter_interface     $formatter;

	//!Class constructor.
	public function		__construct(
		formatter_interface $_formatter,
		string $_hostname,
		int $_port=514
	) {

		$this->hostname=$_hostname;
		$this->port=$_port;
		$this->formatter=$_formatter;
	}

	//!Sets the hostname for the remote log system.
	public function		set_hostname(string $_val) {

		$this->hostname=$_val;
		return $this;
	}

	//!Sets the port for the remote log system.
	public function		set_port(int $_val) {

		$this->port=$_val;
		return $this;
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

	//!Sends the log message to the remote server after composing it. Will
	//!return true or false.
	private function 	send_log(
		int $_level,
		string $_message,
		string $_module
	){

		$errno=0;
		$errstr="";

		$sock=fsockopen('udp://'.$this->hostname,
			$this->port,
			$errno, $errstr, //TODO... This is mostly ignored
			1.0
		);

		if(false===$sock) {

			return false;
		}

		$log_message = $this->formatter->format($_message, $_level, $_module);

		$priority=8+$_level;
		$sock_msg="<{$priority}>".$log_message;

		if(false==fwrite($sock_msg, $sock_msg)) {

			return $this;
		}

		fclose($sock);
		return $this;
	}
}
