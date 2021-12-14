<?php
declare(strict_types=1);
namespace srouter;

/**
*simple http response header.
*/

class http_response_header {

	use \srouter\traits\strict;

	public function __construct(
		string $_name,
		string $_value
	) {

		$this->name=$_name;
		$this->value=$_value;
	}

/**
*returns the header name.
*/
	public function get_name() : string {

		return $this->name;
	}

/**
*returns the header value and optional pieces.
*/
	public function get_value() : string {

		return $this->value;
	}

/**
*outputs the header as expected by a browser.
*/
	public function __toString() : string {

		return strtolower($this->name).":".$this->value;
	}

	private string $name;
	private string $value;
}
