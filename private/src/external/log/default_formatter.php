<?php
declare(strict_types=1);
namespace log;

class default_formatter implements formatter_interface {

	public function format(
		string $_message,
		int $_level,
		string $_module
	) {
		return date($this->get_date_format())." [".$this->level($_level)."] <".$this->module($_module).">: ".$this->message($_message) . PHP_EOL;
	}


	public function get_date_format() {

		return "Y-m-d H:i:s";
	}

	public function level(
		int $_level
	) {

		switch($_level){
			case LOG_EMERG:		return "EMERGENCY";
			case LOG_ALERT:		return "ALERT";
			case LOG_CRIT:		return "CRITICAL";
			case LOG_ERR:		return "ERROR";
			case LOG_WARNING:	return "WARNING";
			case LOG_NOTICE:	return "NOTICE";
			case LOG_INFO:		return "INFO";
			case LOG_DEBUG:		return "DEBUG";
			default:			return "???";
		}
	}

	public function module(
		string $_module
	) {

		return $_module;
	}

	public function message(
		string $_message
	) {

		return is_scalar($_message)
			? $_message
			: json_encode($_message);
	}
}
