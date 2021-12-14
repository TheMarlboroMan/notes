<?php
declare(strict_types=1);
namespace log;

/**
*a formatter interface instead of a format string, to make this a bit more
*flexible.
*/

interface formatter_interface {

/**
*must return the string with the log formated
*WARNING: MAKE SURE THIS STRING ENDS IN A NEW LINE IF YOU WANT YOUR LOG TO BE
*READABLE!
*/
	public function format(
		string $_message,
		int $_level,
		string $_module
	);

	/**
	*must return a string with the date format for the logger.
	*/
	public function get_date_format();

	/**
	*must return a string to translate a level to.
	*/
	public function level(
		int $_level
	);

	/**
	*must return a string with the module, can add stuff before and after it.
	*will only be called if the string has length.
	*/
	public function module(
		string $_module
	);

	/**
	*must return the formatted message (e.g. formatted as json if object or array)
	*/
	public function message(
		string $_message
	);

}
