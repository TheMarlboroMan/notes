<?php
declare(strict_types=1);
namespace srouter\interfaces;

/**
*describes a class that is capable to handle exceptions and errors
*/

interface exception_handler {

/**
*handle the exception hierarchy. Must return null if the exception is not handled or a
*response if it was.
*/
	public function handle_exception(
		\Exception $_e,
		?\srouter\interfaces\request $_request,
		?\srouter\route $_route
	) : ?\srouter\http_response;

/**
*handles the error hierarchy. Must return null if the exception is not handled or a response
*if it was.
*/

	public function handle_error(
		\Error $_e,
		?\srouter\interfaces\request $_request,
		?\srouter\route $_route
	) : ?\srouter\http_response;
}
