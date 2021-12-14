<?php
declare(strict_types=1);
namespace srouter\interfaces;

/**
*Provides an interface for an HTTP request.
*/
interface request {

/**
*must return the URI.
*/
	public function         get_uri() : string;

/**
*must return the HTTP method.
*/
	public function         get_method() : string;

/**
*must return true if encoded as multipart.
*/
	public function         is_multipart() : bool;

/**
/*must return the full body of the request.
*/
	public function         get_body() : string;

/**
/*must return true if a part of the body with such name exists.
*/
	public function         part_exists(string $_name) : bool;

/**
/*must return the multipart body fragment as a scalar.
*/
	public function         get_part(string $_name) : string;

/**
/*must return true if a named query string value exists.
*/
	public function         has_query(string $_name) : bool;

/**
/*must return the query string named value
*/
	public function         get_query(string $_name) : string;

/**
/*must return the full query string.
*/
	public function         get_query_string() : string;

/**
/*must return true if a named header exists.
*/
	public function         has_header(string $_name) : bool;

/**
/*must return the header value
*/
	public function         get_header(string $_name) : string;
};
