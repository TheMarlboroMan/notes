<?php
declare(strict_types=1);
namespace srouter\exception;

/**
*thrown when an argument type in the request does not match the type
*specified in the parameter (400 error)
*/

class bad_argument_type extends exception {};
