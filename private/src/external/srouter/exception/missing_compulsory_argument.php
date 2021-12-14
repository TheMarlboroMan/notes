<?php
declare(strict_types=1);
namespace srouter\exception;

/**
*thrown when an argument is not optional and has not been provided.
*/
class missing_compulsory_argument extends exception {

	public function __construct(string $_name) {

		parent::__construct("missing compulsory argument '$_name'");
	}
};
