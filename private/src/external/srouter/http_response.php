<?php
declare(strict_types=1);
namespace srouter;

/**
*http_response as defined by status code, http_headers and body (a string).
*/

class http_response {

	use \srouter\traits\strict;

	public function     __construct(
		int $_code,
		array $_headers, //of http_response_header type!
		string $_body
	) {

		$this->status_code=$_code;
		$this->headers=$_headers;
		$this->body=$_body;
	}

/**
*outputs the response, starting with the headers and ending with the body.
*this should be the last thing to happen in your application.
*/
	public function     out() : void {

		header("HTTP/1.1 {$this->status_code} {$this->translate_code($this->status_code)}");
		foreach($this->headers as $header) {
			header((string)$header);
		}

		echo $this->body.PHP_EOL;
	}

/**
*returns a string representation of the response for whatever purposes one
*can think of.
*/
	public function     toString() : string {

		$headers=array_reduce(
			$this->headers,
			function(string $_carry, \srouter\http_response_header $_header) : string {
				return $_carry.$_header.PHP_EOL;
			},
			""
		);

		return <<<R
HTTP/1.1 {$this->status_code} {$this->translate_code($this->status_code)}
$headers

$this->body

R;
	}


	public const code_100_continue=100;
	public const code_101_switching_protocols=101;
	public const code_200_ok=200;
	public const code_201_created=201;
	public const code_202_accepted=202;
	public const code_203_non_authoritative_information=203;
	public const code_204_no_content=204;
	public const code_205_reset_content=205;
	public const code_206_partial_content=206;
	public const code_300_multiple_choices=300;
	public const code_302_found=302;
	public const code_303_see_other=303;
	public const code_304_not_modified=304;
	public const code_305_use_proxy=305;
	public const code_400_bad_request=400;
	public const code_401_unauthorized=401;
	public const code_402_payment_required=402;
	public const code_403_forbidden=403;
	public const code_404_not_found=404;
	public const code_405_method_not_allowed=405;
	public const code_406_not_acceptable=406;
	public const code_407_proxy_authentication_required=407;
	public const code_408_request_timeout=408;
	public const code_409_conflict=409;
	public const code_410_gone=410;
	public const code_411_length_required=411;
	public const code_412_precondition_failed=412;
	public const code_413_request_entity_too_large=413;
	public const code_414_request_uri_too_long=414;
	public const code_415_unsupported_media_type=415;
	public const code_416_requested_range_not_satisfiable=416;
	public const code_417_expectation_failed=417;
	public const code_418_i_am_a_teapot=418;
	public const code_500_internal_server_error=500;
	public const code_501_not_implemented=501;
	public const code_502_bad_gateway=502;
	public const code_503_service_unavailable=503;
	public const code_504_gateway_timeout=504;
	public const code_505_http_version_not_supported=505;

	public static function is_info(
		int $_code
	) : bool {

		return $_code >= 100 && $_code <= 199;
	}

	public static function is_ok(
		int $_code
	) : bool {

		return $_code >= 200 && $_code <= 299;
	}

	public static function is_redirect(
		int $_code
	) : bool {

		return $_code >= 300 && $_code <= 399;
	}

	public static function is_client_error(
		int $_code
	) : bool {

		return $_code >= 400 && $_code <= 499;
	}

	public static function is_server_error(
		int $_code
	) : bool {

		return $_code >= 500 && $_code <= 599;
	}

	private function translate_code(
		int $_code
	) : string{

		$message="";
		switch($_code) {
			case self::code_100_continue:                           return 'Continue';
			case self::code_101_switching_protocols:                return 'Switching Protocols';
			case self::code_200_ok:                                 return 'OK';
			case self::code_201_created:                            return 'Created';
			case self::code_202_accepted:                           return 'Accepted';
			case self::code_203_non_authoritative_information:      return 'Non-Authoritative Information';
			case self::code_204_no_content:                         return 'No Content';
			case self::code_205_reset_content:                      return 'Reset Content';
			case self::code_206_partial_content:                    return 'Partial Content';
			case self::code_300_multiple_choices:                   return 'Multiple Choices';
			case self::code_302_found:                              return 'Found';
			case self::code_303_see_other:                          return 'See Other';
			case self::code_304_not_modified:                       return 'Not Modified';
			case self::code_305_use_proxy:                          return 'Use Proxy';
			case self::code_400_bad_request:                        return 'Bad Request';
			case self::code_401_unauthorized:                       return 'Unauthorized';
			case self::code_402_payment_required:                   return 'Payment Required';
			case self::code_403_forbidden:                          return 'Forbidden';
			case self::code_404_not_found:                          return 'Not Found';
			case self::code_405_method_not_allowed:                 return 'Method Not Allowed';
			case self::code_406_not_acceptable:                     return 'Not Acceptable';
			case self::code_407_proxy_authentication_required:      return 'Proxy Authentication Required';
			case self::code_408_request_timeout:                    return 'Request Timeout';
			case self::code_409_conflict:                           return 'Conflict';
			case self::code_410_gone:                               return 'Gone';
			case self::code_411_length_required:                    return 'Length Required';
			case self::code_412_precondition_failed:                return 'Precondition Failed';
			case self::code_413_request_entity_too_large:           return 'Request Entity Too Large';
			case self::code_414_request_uri_too_long:               return 'Request-URI Too Long';
			case self::code_415_unsupported_media_type:             return 'Unsupported Media Type';
			case self::code_416_requested_range_not_satisfiable:    return 'Requested Range Not Satisfiable';
			case self::code_417_expectation_failed:                 return 'Expectation Failed';
			case self::code_418_i_am_a_teapot:                      return 'I am a teapot';
			case self::code_500_internal_server_error:              return 'Internal Server Error';
			case self::code_501_not_implemented:                    return 'Not Implemented';
			case self::code_502_bad_gateway:                        return 'Bad Gateway';
			case self::code_503_service_unavailable:                return 'Service Unavailable';
			case self::code_504_gateway_timeout:                    return 'Gateway Timeout';
			case self::code_505_http_version_not_supported:         return 'HTTP Version Not Supported';
			default:                                                return $_code;
		}
	}

	private int         $status_code;
	private array       $headers;
	private string      $body;
}
