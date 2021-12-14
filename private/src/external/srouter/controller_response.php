<?php
declare(strict_types=1);
namespace srouter;

/**
*intermediate response class that can be sent to out transformers so they
*become http responses. unlike the http response, the body can still be
*anything.
*/

class controller_response {

	use \srouter\traits\strict;

	public function     __construct(
		int $_code,
		array $_headers, //of http_response_header type!
		$_body
	) {

		$this->status_code=$_code;
		$this->headers=$_headers;
		$this->body=$_body;
	}

/**
*returns the status code, which should really be the same as a standard HTTP
*status code.
*/
	public function     get_status_code() : int{

		return $this->status_code;
	}

/**
*returns the headers. The whole system expects these to be of
*http_response_header type.
*/
	public function     get_headers() : array {

		return $this->headers;
	}

/**
*returns the body, which can literally be anything that your out_transformers
*will be able to work with.
*/
	public function     get_body() {

		return $this->body;
	}

	private int         $status_code;
	private array       $headers;
	private             $body;
}
